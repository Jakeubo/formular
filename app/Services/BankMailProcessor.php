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
            Log::info('BankMail: dostupná složka -> ' . $f->path);
        }

        // ✅ zkusíme nejdřív INBOX
        // místo INBOX:
        try {
            $folder = $client->getFolder('Bank');

            // vezmeme posledních 50 zpráv od včerejška
            $messages = $folder->messages()
                ->all()
                ->limit(50)
                ->get();

            Log::info('BankMail: počet zpráv ve složce Bank -> ' . $messages->count());
        } catch (\Exception $e) {
            Log::error('BankMail: chyba při čtení složky Bank -> ' . $e->getMessage());
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
        preg_match('/Variabilní symbol:\s*(\d+)/u', $bodyText, $vsMatch);
        $variableSymbol = $vsMatch[1] ?? null;

        if (preg_match('/(?:platba ve výši|Částka[^:]*:)\s*([\d\s,.]+)\s*Kč/u', $bodyText, $amountMatch)) {
            $amountStr = str_replace([' ', ','], ['', '.'], $amountMatch[1]);
            $amount = (float) $amountStr;
        } else {
            $amount = null;
        }

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

        if ($amount) {
            // vytvoříme nebo najdeme platbu
            $payment = BankPayment::firstOrCreate(
                ['message_id' => $messageId],
                [
                    'variable_symbol' => $variableSymbol,
                    'amount'          => $amount,
                    'account_number'  => $accountNumber,
                    'raw_text'        => $bodyText,
                    'received_at'     => $receivedAt,
                ]
            );

            // pokud je faktura k dispozici a sedí částka, označíme ji jako zaplacenou
            if ($variableSymbol) {
                $invoice = Invoice::where('variable_symbol', $variableSymbol)
                    ->where('status', '!=', 'paid')
                    ->first();

                if ($invoice && (float) $invoice->total_price == $amount) {
                    $invoice->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                    ]);
                    Log::info("BankMail: faktura {$invoice->invoice_number} označena jako zaplacená.");
                }
            }

            // vrátíme true jen pokud je to opravdu nová platba
            return $payment->wasRecentlyCreated;
        }

        Log::warning("BankMail: nepodařilo se vyparsovat částku.");
        return false;
    }
}
