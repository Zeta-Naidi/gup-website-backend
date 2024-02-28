<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceFetcher implements ShouldQueue
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
      case 'INSERT_DEVICES_INTERVAL':
        $this->_insertDevicesInterval();
        break;
      default:
        break;
    }
  }


  private function _insertDevicesInterval()
  {
    try {
      $devicesToInsert = $this->_filterDevicesDuplicated($this->params['devices']);
      $serialOfDevicesToInsert = array_map(fn($el) => $el["serialNumber"], $devicesToInsert);
      //Update devices already present
      $devicesToUpdate = Device::on($this->dbConnection)
        ->whereIn("serialNumber", $serialOfDevicesToInsert)->get();

      foreach ($devicesToUpdate as $deviceToUpdate) {
        $deviceWithNewInfo = $devicesToInsert[$deviceToUpdate["serialNumber"]];
        $deviceToUpdate->update([
          "clientId" => $this->params["clientId"],
          "name" => $deviceWithNewInfo["deviceName"],
          "osVersion" => $deviceWithNewInfo["osVersion"],
          "isEnrolled" => $deviceWithNewInfo["isEnrolled"],
          "isSupervised" => $deviceWithNewInfo["isSupervised"],
          "isAgentOn" => $deviceWithNewInfo["isAgentOn"],
        ]);
        unset($devicesToInsert[$deviceToUpdate["serialNumber"]]);
      }
      //create new ones if there are ones
      if (count($devicesToInsert) > 0) {
        DB::connection($this->dbConnection)->table('devices')
          ->insert(
            array_map(function ($deviceToInsert) {
              return [
                "clientId" => $this->params["clientId"],
                "serialNumber" => $deviceToInsert["serialNumber"],
                "name" => $deviceToInsert["deviceName"],
                "osType" => $deviceToInsert["osType"],
                "osVersion" => $deviceToInsert["osVersion"],
                "isEnrolled" => $deviceToInsert["isEnrolled"],
                "isSupervised" => $deviceToInsert["isSupervised"],
                "isAgentOn" => $deviceToInsert["isAgentOn"],
              ];
            }, $devicesToInsert)
          );
        dispatch(new TryAdoptOrphanEvents(
          dbConnection: $this->dbConnection,
          serialNumbers: array_map(fn($el) => $el["serialNumber"], $devicesToInsert)),
        );
      }

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }

  private function _filterDevicesDuplicated($arrayToFilter)
  {
    $hashTable = [];
    foreach ($arrayToFilter as $el) {
      if (!isset($hashTable[$el["serialNumber"]]))
        $hashTable[$el["serialNumber"]] = $el;
      else {
        $dateStored = new \DateTime($hashTable[$el["serialNumber"]]["lastMdmUpdateAt"]);
        $dateToCheck = new \DateTime($el["lastMdmUpdateAt"]);
        if ($dateToCheck >= $dateStored)
          $hashTable[$el["serialNumber"]] = $el;
      }
    }
    return $hashTable;
  }

}
