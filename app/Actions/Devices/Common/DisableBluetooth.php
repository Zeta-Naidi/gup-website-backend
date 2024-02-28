<?php

namespace App\Actions\Devices\Common;

use App\Actions\Devices\IActionSourceDevices;
use App\Dtos\Controller\ControllerResponse;
use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use App\Services\DatabaseDataRetriever;
use Illuminate\Support\Facades\Log;

class DisableBluetooth implements IActionSourceDevices
{
  public array $osType = ["Apple", "Windows"];
  // public string $osVersion;
  // public string $license;
  // public int $agentVersion;

  private string $actionType = "DisableBluetooth";
  public string $actionIdentifier = "DisableBluetooth_ID";
  private bool $actionStatusActive = true;

  public function actionData(array $device): array
  {
    return [
      'actionType' => $this->actionType,
      'actionIdentifier' => $this->actionIdentifier
    ];
  }

  public function checkCompatibility(array $device, array $license): bool
  {
    // TODO: check compatibility:
    // - check action not disabled
    // - device osType
    // - device isEnrolled
    // - device androidAgentVersion
    // - device isSupervised
    // - (tutte le altre condizioni vanno scritte e commentate)

    try {

      if (!in_array('LICENSE_ADVANCED_EDU_ACTIVE', $license) || !in_array('LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE', $license)) {
        return false;
      }

      if (
        !$this->actionStatusActive ||
        !in_array(ucfirst($device['osType']), $this->osType)
        && (
          (
            $device['osType'] === 'ios' &&
            $device['isEnrolled'] === 1 &&
            $device['isSupervised'] === 1 &&
            $device['assignedLicense'] > 0
            //$device['modelName'] != DeviceModel::AppleTV
            //$device['isBluetoothActive'], 'true_false', true
          )
          ||
          (
            $device['osType'] === 'windows' &&
            $device['isEnrolled'] === 1 &&
            $device['assignedLicense'] > 0
            //$device['isBluetoothActive'], 'true_false', true
          )
        )
      ) {
        return false;
      }

    } catch (\Exception $exception) {
      // handle
    }


    return true;
  }

  public function execute(array $device, string $params = null): ControllerResponse
  {
    // TODO => store in DB and send push notification ???
    try {
      $newAction = new ActionEntity([
        'type' => $this->actionIdentifier, 'deviceId' => $device['id'], 'params' => $params,
        'status' => 1, 'errorDetail' => null, 'sentAt' => null, 'createdAt' => now()]);
      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      return $repository->store($newAction)->formatControllerResponse();
    } catch (\Exception $exception) {
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }
}
