<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DeleteOldUserDevices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      try {
        $thirtyDaysAgo = date("Y-m-d H:i:s", strtotime('-30 days'));
        DB::table('user_devices')
          ->where("createdAt", '<', $thirtyDaysAgo)
          ->delete();
      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }
    }
}
