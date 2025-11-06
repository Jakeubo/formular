<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Jobs\SendSatisfactionEmail;
use Illuminate\Support\Facades\Log;

class ScheduleSatisfactionEmail
{
    public function handle(OrderShipped $event)
    {
        $invoice = $event->order->invoice ?? null;

        if (!$invoice) {
            Log::warning('Žádná faktura k objednávce, e-mail spokojenosti nelze odeslat.');
            return;
        }

        if ($invoice->feedback_sent_at) {
            Log::info("Feedback e-mail už byl odeslán pro fakturu ID {$invoice->id}");
            return;
        }

        SendSatisfactionEmail::dispatch($invoice)
            ->delay(now()->addDays(4));
    }
}
