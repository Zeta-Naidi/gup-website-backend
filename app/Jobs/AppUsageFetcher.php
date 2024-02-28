<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AppUsageFetcher implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $dbConnection;
  private $methodToExecute;
  private $params;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($dbConnection = '', $methodToExecute = '', $params = [])
  {
    $this->dbConnection = $dbConnection;
    $this->methodToExecute = $methodToExecute;
    $this->params = $params;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    switch ($this->methodToExecute) {
      case 'INSERT_APP_USAGES':
        $this->_insertAppUsages();
        break;
      default:
        break;
    }
  }

  private function _insertAppUsages()
  {
    try {
      $appUsagesToInsert = $this->params['payload'];
      $clientId = $this->params['clientId'];
      DB::connection($this->dbConnection)->table('app_usages')
        ->insert(
          array_map(function ($appUsage) use ($clientId) {
            return [
              "deviceSerialNumber" => $appUsage['serialNumber'],
              "clientId" => $clientId,
              "packageName" => $appUsage['packageName'],
              "usageTime" => $appUsage['usageTime'],
              "firstTimestamp" => $appUsage['firstTimestamp'],
              "lastTimestamp" => $appUsage['lastTimestamp'],
            ];
          }, $appUsagesToInsert)
        );
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }
}
