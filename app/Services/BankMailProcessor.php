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

        // složka, kde máš bankovní maily
        $folder = $client->getFolder('Bank');
        $messages = $folder->query()->all()->limit(10)->get();

        foreach ($messages as $msg) {
            $from       = $msg->getFrom()[0]->mail ?? null;
            $subject    = $msg->getSubject();
            $receivedAt = $msg->getDate();      // 👈 čas přijetí mailu
            $messageId  = $msg->getMessageId(); // 👈 jedinečný ID e-mailu

            // 👉 tělo e-mailu – preferujeme HTML, pak text
            $body = $msg->getHTMLBody() ?: $msg->getTextBody() ?: $msg->getRawBody();

            // 👉 vyčištění HTML (odstranění <style>, <script>, tagů)
            $bodyClean = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $body);
            $bodyClean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $bodyClean);
            $bodyText  = strip_tags(html_entity_decode($bodyClean));

            // log pro ladění
            Log::info('BankMail: kontrola mailu', [
                'from'      => $from,
                'subject'   => $subject,
                'date'      => $receivedAt,
                'messageId' => $messageId,
            ]);

            if ($this->handleEmail($bodyText, $receivedAt, $messageId)) {
                $processed++;
            }

            // označíme jako přečtený
            $msg->setFlag('Seen');
        }

        return $processed;
    }

    private function handleEmail(string $bodyText, $receivedAt, ?string $messageId): bool
    {
        // najdi variabilní symbol (může chybět)
        preg_match('/Variabilní symbol:\s*(\d+)/u', $bodyText, $vsMatch);
        $variableSymbol = $vsMatch[1] ?? null;

        // najdi částku
        preg_match('/Částka[^:]*:\s*([\d\s,]+)\s*Kč/u', $bodyText, $amountMatch);
        $amount = isset($amountMatch[1])
            ? floatval(str_replace(',', '.', str_replace(' ', '', $amountMatch[1])))
            : null;

        // najdi číslo účtu protistrany
        preg_match('/Číslo účtu protistrany:\s*([\d\/]+)/u', $bodyText, $accMatch);
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
                    ['message_id' => $messageId], // 👈 unikátní podle message_id
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

        // nic jsme nevyparsovali
        Log::warning("BankMail: nepodařilo se vyparsovat částku.");
        return false;
    }
}
