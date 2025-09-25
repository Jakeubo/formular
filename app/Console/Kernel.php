<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definuj příkazy plánovače.
     */
    protected function schedule(Schedule $schedule): void
    {
        // náš příkaz pro kontrolu plateb
        $schedule->command('bank:check-payments')->everyFiveMinutes();
    }

    /**
     * Zaregistruj příkazy aplikace.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
