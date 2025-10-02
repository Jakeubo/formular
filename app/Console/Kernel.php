<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definuj příkazy plánovače.
     */
    protected function schedule(Schedule $schedule)
    {
        // 🔁 spustí každých 10 minut
        $schedule->command('bank:check-payments')->everyTenMinutes();

        // případně test jen na log
        // $schedule->command('inspire')->everyMinute();
    }
    /**
     * Zaregistruj příkazy aplikace.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
