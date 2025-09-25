<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMail extends Command
{
    /**
     * Název příkazu
     *
     * @var string
     */
    protected $signature = 'mail:test {address}';

    /**
     * Popis příkazu
     *
     * @var string
     */
    protected $description = 'Pošle testovací e-mail na zadanou adresu';

    /**
     * Spuštění příkazu
     */
    public function handle()
    {
        $to = $this->argument('address');

        Mail::raw('Testovací zpráva ze ZapichniTo3D – kontrola SPF/DKIM/DMARC.', function ($msg) use ($to) {
            $msg->to($to)->subject('Test ze ZapichniTo3D');
        });

        $this->info("✅ Testovací e-mail byl odeslán na {$to}");
    }
}
