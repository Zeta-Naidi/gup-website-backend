<?php

namespace App\Jobs;

use App\Models\AccessLog;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClearOldEventsAndLogs implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      $allDistributorConnections = array_filter(config('database.connections'),fn($el) =>  isset($el['type']) && $el['type'] == 'distributor');
      $allConnectionsFiltered = [];
      foreach ($allDistributorConnections as $key => $connection) {
        if ($key != config('database.default') && $key != 'sqlite_testing')
          $allConnectionsFiltered[] = $key;
      }
      $sixtyDaysAgo = date("Y-m-d H:i:s", strtotime('-60 days'));
      foreach ($allConnectionsFiltered as $connection) {
        Event::on($connection)->whereDate('detectionDate', '<', $sixtyDaysAgo)->delete();
      }
      AccessLog::on(config('database-default'))
        ->whereDate('createdAt', '<', $sixtyDaysAgo)->delete();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }
}
