<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BankMailProcessor;

class CheckBankPayments extends Command
{
    /**
     * Název příkazu pro artisan
     *
     * @var string
     */
    protected $signature = 'bank:check-payments';

    /**
     * Popis příkazu
     *
     * @var string
     */
    protected $description = 'Načte nové e-maily z banky a spáruje je s fakturami';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processor = new BankMailProcessor();
        $count = $processor->process();

        if ($count > 0) {
            $this->info("✅ Zpracováno $count nových plateb.");
        } else {
            $this->warn("ℹ️ Nebyly nalezeny žádné nové platby.");
        }
    }
}
