<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BankMailProcessor;

class CheckBankPayments extends Command
{
    // název příkazu → voláme "php artisan bank:check"
    protected $signature = 'bank:check';

    protected $description = 'Zkontroluje nové bankovní platby v e-mailu a spáruje s fakturami';

    public function handle()
    {
        $count = (new BankMailProcessor())->process();
        $this->info("✅ Zpracováno {$count} nových plateb.");
    }
}
