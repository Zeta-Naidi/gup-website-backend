<?php

namespace App\Jobs;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NetworkActivityFetcher implements ShouldQueue
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
      case 'INSERT_NETWORK_ACTIVITIES':
        $this->_insertNetworkActivities();
        break;
      default:
        break;
    }
  }

  private function _insertNetworkActivities()
  {
    try {
      $networkActivitiesToInsert = $this->params['payload'];
      $clientId = $this->params['clientId'];
      DB::connection($this->dbConnection)->table('network_activities')
        ->insert(
          array_map(function ($networkActivity) use ($clientId) {
            return [
              "deviceSerialNumber" => $networkActivity['serialNumber'],
              "clientId" => $clientId,
              "packageName" => $networkActivity['packageName'],
              "bytesIn" => $networkActivity['bytesIn'],
              "bytesOut" => $networkActivity['bytesOut'],
              "firstTimestamp" => $networkActivity['firstTimestamp'],
              "lastTimestamp" => $networkActivity['lastTimestamp'],
            ];
          }, $networkActivitiesToInsert)
        );

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }
}
