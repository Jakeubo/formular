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

    // ✅ log připojení
    if ($client->isConnected()) {
        Log::info('BankMail: IMAP připojení OK');
    } else {
        Log::error('BankMail: IMAP připojení selhalo');
        return 0;
    }

    // ✅ vypíšeme všechny složky
    foreach ($client->getFolders() as $f) {
        Log::info('BankMail: dostupná složka -> '.$f->path);
    }

    // ✅ zkusíme nejdřív INBOX
// místo INBOX:
try {
    $folder = $client->getFolder('Bank'); // 👈 správná složka
    $messages = $folder->messages()->unseen()->limit(10)->get();
    Log::info('BankMail: počet zpráv ve složce Bank -> '.$messages->count());
} catch (\Exception $e) {
    Log::error('BankMail: chyba při čtení složky Bank -> '.$e->getMessage());
    return 0;
}

    // ✅ zpracujeme zprávy
    foreach ($messages as $msg) {
        $from       = $msg->getFrom()[0]->mail ?? null;
        $subject    = $msg->getSubject();
        $receivedAt = $msg->getDate();
        $messageId  = $msg->getMessageId();

        $body = $msg->getHTMLBody() ?: $msg->getTextBody() ?: $msg->getRawBody();
        $bodyClean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $body);
        $bodyClean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $bodyClean);
        $bodyText  = strip_tags(html_entity_decode($bodyClean));

        Log::info('BankMail: kontrola mailu', [
            'from'      => $from,
            'subject'   => $subject,
            'date'      => $receivedAt,
            'messageId' => $messageId,
        ]);

        if ($this->handleEmail($bodyText, $receivedAt, $messageId)) {
            $processed++;
        }

        $msg->setFlag('Seen');
    }

    Log::info("BankMail: celkem zpracováno -> {$processed}");
    return $processed;
}


    private function handleEmail(string $bodyText, $receivedAt, ?string $messageId): bool
    {
        // 🔎 variabilní symbol (může chybět)
        preg_match('/Variabilní symbol:\s*(\d+)/u', $bodyText, $vsMatch);
        $variableSymbol = $vsMatch[1] ?? null;

        // 🔎 částka – zvládne "platba ve výši 5 000,00 Kč" i "Částka: 326,00 Kč"
        if (preg_match('/(?:platba ve výši|Částka[^:]*:)\s*([\d\s,.]+)\s*Kč/u', $bodyText, $amountMatch)) {
            $amountStr = str_replace(' ', '', $amountMatch[1]); // 5 000,00 -> 5000,00
            $amountStr = str_replace(',', '.', $amountStr);     // 5000,00 -> 5000.00
            $amount = (float) $amountStr;
        } else {
            $amount = null;
        }

        // 🔎 číslo účtu protistrany – včetně předčíslí
        preg_match('/Číslo účtu protistrany:\s*([\d\-]+\/\d{4})/u', $bodyText, $accMatch);
        $accountNumber = $accMatch[1] ?? null;

        Log::info('BankMail: parsování', [
            'vs'         => $variableSymbol,
            'amount_raw' => $amountMatch[1] ?? null,
            'amount'     => $amount,
            'account'    => $accountNumber,
            'received'   => $receivedAt,
            'message_id' => $messageId,
        ]);

        // 👉 pokud je VS a částka → zkusíme spárovat fakturu
        if ($variableSymbol && $amount) {
            $invoice = Invoice::where('variable_symbol', $variableSymbol)
                ->where('status', '!=', 'paid')
                ->first();

            if ($invoice && (float)$invoice->total_price == $amount) {
                $invoice->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info("BankMail: faktura {$invoice->invoice_number} označena jako zaplacená.");
                return true;
            } else {
                Log::warning("BankMail: nenašla se faktura pro VS {$variableSymbol} a částku {$amount}.");

                // uložíme platbu, i když nesedí
                BankPayment::firstOrCreate(
                    ['message_id' => $messageId], // unikátní podle message_id
                    [
                        'variable_symbol' => $variableSymbol,
                        'amount'          => $amount,
                        'account_number'  => $accountNumber,
                        'raw_text'        => $bodyText,
                        'received_at'     => $receivedAt,
                    ]
                );

                return true;
            }
        }

        // 👉 když není VS, ale máme částku – uložíme platbu do bank_payments
        if ($amount) {
            Log::warning("BankMail: platba bez VS. Částka: {$amount}");

            BankPayment::firstOrCreate(
                ['message_id' => $messageId],
                [
                    'variable_symbol' => null,
                    'amount'          => $amount,
                    'account_number'  => $accountNumber,
                    'raw_text'        => $bodyText,
                    'received_at'     => $receivedAt,
                ]
            );

            return true;
        }

        // ❌ nic jsme nevyparsovali
        Log::warning("BankMail: nepodařilo se vyparsovat částku.");
        return false;
    }
}
