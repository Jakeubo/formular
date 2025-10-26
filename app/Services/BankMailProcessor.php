<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\BankPayment;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;

class BankMailProcessor
{
    public function process(): int
    {
        $processed = 0;

        $client = Client::account('default');
        $client->connect();

        if (!$client->isConnected()) {
            Log::error('BankMail: IMAP připojení selhalo');
            return 0;
        }

        Log::info('BankMail: IMAP připojeno OK');

        try {
            // ⬇️ zkusíme více variant složek
            $folders = ['Bank', 'INBOX.Bank', 'Inbox', 'INBOX'];
            $folder = null;
            foreach ($folders as $f) {
                try {
                    $folder = $client->getFolder($f);
                    Log::info("BankMail: nalezena složka -> {$f}");
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$folder) {
                Log::error('BankMail: žádná platná složka nenalezena');
                return 0;
            }

            $messages = $folder->messages()
                ->all()
                ->limit(500)
                ->get();
        } catch (\Exception $e) {
            Log::error('BankMail: chyba při čtení složky -> ' . $e->getMessage());
            return 0;
        }

        Log::info('BankMail: načteno ' . $messages->count() . ' zpráv.');

        $knownSymbols = Invoice::pluck('variable_symbol')->filter()->toArray();

        foreach ($messages as $msg) {
            $messageId  = $msg->getMessageId();
            $receivedAt = $msg->getDate();

            $body = $msg->getHTMLBody() ?: $msg->getTextBody() ?: $msg->getRawBody();
            $bodyText = strip_tags(html_entity_decode($body));

            $result = $this->handleEmail($bodyText, $receivedAt, $messageId, $knownSymbols);

            if ($result) {
                $processed++;
                Log::info("BankMail: ✅ nová platba uložena");
            } else {
                Log::info("BankMail: ⏭️ zpráva přeskočena");
            }

            $msg->setFlag('Seen');
        }

        Log::info("BankMail: celkem zpracováno -> {$processed}");
        return $processed;
    }

    private function handleEmail(string $bodyText, $receivedAt, ?string $messageId, array $knownSymbols): bool
    {
        preg_match('/(?:Variabilní symbol|VS)\s*[:.]?\s*(\d{3,10})/iu', $bodyText, $vsMatch);
        $variableSymbol = $vsMatch[1] ?? null;

        if (!$variableSymbol) {
            Log::warning("BankMail: žádný VS nenalezen");
            return false;
        }

        if (!in_array($variableSymbol, $knownSymbols)) {
            Log::info("BankMail: VS {$variableSymbol} nepatří k našim fakturám");
            return false;
        }

        if (preg_match('/(?:platba ve výši|Částka[^:]*:)\s*([\d\s,.]+)\s*Kč/u', $bodyText, $amountMatch)) {
            $amount = $this->parseAmount($amountMatch[1]);
        } else {
            $amount = null;
            Log::warning("BankMail: částka nenalezena pro VS {$variableSymbol}");
        }

        // Nejprve zkusíme najít "Číslo účtu protistrany"
        if (preg_match('/Číslo účtu protistrany[:.]?\s*([\d\-]+\/\d{4})/iu', $bodyText, $accMatch)) {
            $accountNumber = $accMatch[1];
        } elseif (preg_match('/Číslo účtu[:.]?\s*([\d\-]+\/\d{4})/iu', $bodyText, $accMatch)) {
            // fallback – když by "protistrany" chybělo
            $accountNumber = $accMatch[1];
        } else {
            $accountNumber = null;
        }

        // $accountNumber = $accMatch[1] ?? null;

        $payment = BankPayment::firstOrCreate(
            ['message_id' => $messageId],
            [
                'variable_symbol' => $variableSymbol,
                'amount'          => $amount,
                'account_number'  => $accountNumber,
                'raw_text'        => mb_substr($bodyText, 0, 2000),
                'received_at'     => $receivedAt,
            ]
        );

        // 🚫 Pokud platba už dříve existovala, nezasahujeme do faktury
        if (!$payment->wasRecentlyCreated) {
            Log::info("BankMail: platba s VS {$variableSymbol} už existovala – faktura se nemění");
            return false;
        }

        // ✅ Platba je nová – teď teprve kontrolujeme fakturu
        $invoice = Invoice::where('variable_symbol', $variableSymbol)->first();
        if ($invoice && $amount !== null) {
            $invoiceAmount = (float)$invoice->total_price;

            // ✅ pokud zákazník zaplatil alespoň fakturovanou částku, bereme jako zaplaceno
            if ($amount >= $invoiceAmount) {
                $status = mb_strtolower(trim($invoice->status));

                if ($status === 'shipped') {
                    Log::info("BankMail: faktura {$invoice->invoice_number} přeskočena – již odesláno");
                } elseif ($status === 'paid') {
                    Log::info("BankMail: faktura {$invoice->invoice_number} již zaplacená, bez změny");
                } else {
                    $invoice->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                    ]);
                    $diff = number_format($amount - $invoiceAmount, 2, ',', ' ');
                    Log::info("BankMail: faktura {$invoice->invoice_number} označena jako zaplacená (VS {$variableSymbol}), zaplaceno o {$diff} Kč víc");
                }
            } else {
                $diff = number_format($invoiceAmount - $amount, 2, ',', ' ');
                Log::warning("BankMail: částka je o {$diff} Kč nižší – faktura {$invoice->invoice_number} zůstává nezaplacená");
            }
        }


        return true;
    }


    private function parseAmount(string $raw): ?float
    {
        $clean = preg_replace('/[^\d,.-]/', '', $raw);
        $clean = str_replace(',', '.', $clean);

        // odstraníme všechny tečky kromě poslední
        if (substr_count($clean, '.') > 1) {
            $parts = explode('.', $clean);
            $last = array_pop($parts);
            $clean = implode('', $parts) . '.' . $last;
        }

        return is_numeric($clean) ? round((float)$clean, 2) : null;
    }
}
