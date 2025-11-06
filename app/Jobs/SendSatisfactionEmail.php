<?php

namespace App\Jobs;

use App\Mail\SatisfactionEmail;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog;

class SendSatisfactionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle(): void
    {
        $invoice = $this->invoice;

        try {
            Mail::to($invoice->order->email)->send(new SatisfactionEmail($invoice));

            EmailLog::create([
                'to_email' => $invoice->order->email,
                'subject' => 'Jak jste byli spokojeni s objednávkou?',
                'type' => 'SatisfactionEmail',
                'invoice_id' => $invoice->id,
                'sent_at' => now(),
                'success' => true,
            ]);

            $invoice->update(['feedback_sent_at' => now()]);
            Log::info("E-mail spokojenosti odeslán pro fakturu ID {$invoice->id}");
        } catch (\Throwable $e) {
            EmailLog::create([
                'to_email' => $invoice->order->email ?? 'neznámý',
                'subject' => 'Jak jste byli spokojeni s objednávkou?',
                'type' => 'SatisfactionEmail',
                'invoice_id' => $invoice->id ?? null,
                'sent_at' => now(),
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Chyba při odesílání e-mailu spokojenosti: " . $e->getMessage());
        }
    }
}
