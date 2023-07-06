<?php

namespace App\Console;

use App\Jobs\ExcelImportJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('queue:work --queue=imports')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->runInBackground();

        $schedule->call(function () {
            $filePath = storage_path('app/ExcelFolder/catalog.xlsx');

            if (file_exists($filePath)) {
                // Dispatch ExcelChunk job to chunk the file
                ExcelImportJob::dispatch($filePath);
            }
        })->everyMinute();

        // $schedule->command('queue:work --queue=chunkFile')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
