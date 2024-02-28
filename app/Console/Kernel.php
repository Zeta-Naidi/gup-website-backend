<?php

namespace App\Console;

use App\Jobs\ClearExpiredSessionsAndCache;
use App\Jobs\CollectClientData;
use App\Jobs\CollectData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
      try{
        $jobsToBeScheduledAndDecoded = file_get_contents(__DIR__.env('SCHEDULER_FILE_NAME'));
        $jobsToBeScheduled = json_decode($jobsToBeScheduledAndDecoded);
        foreach ($jobsToBeScheduled->jobs as $job){
          $jobName = $job->name;
          $params = $job->params;
          $frequency = $job->frequency;
          $schedule->job(new $jobName(...$params))->$frequency();
        }
      }
      catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }
      /* //$schedule->job(new CollectClientData([],'CLIENT'))->everyMinute();
       $schedule->job(new CollectClientData([],'EVENT_TYPE'))->everyThirtyMinutes();
       $schedule->job(new CollectClientData([],'DEVICE'))->everyMinute();
       //$schedule->job(new CollectClientData([],'NETWORK_ACTIVITY'))->everyMinute();
       //$schedule->job(new CollectClientData([],'APP_USAGE'))->everyMinute();
       $schedule->job(new CollectClientData([],'EVENT'))->everyMinute();
       $schedule->job(new ClearExpiredSessionsAndCache())->everyThirtyMinutes();*/
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
