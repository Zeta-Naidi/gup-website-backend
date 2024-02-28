<?php

namespace App\Actions\Devices\Common;

use App\Actions\Devices\IActionSourceDevices;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use App\Dtos\Controller\ControllerResponse;
use Illuminate\Support\Facades\Log;

class EnableEmergencyMode implements IActionSourceDevices
{
  public array $osType = ["Android", "Windows"];

  private string $actionType = "EnableEmergencyMode";
  public string $actionIdentifier = "EnableEmergencyMode_ID";
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

      if (!in_array('LICENSE_ADVANCED_MTD1_ACTIVE', $license)) {
        return false;
      }

      if (
        !$this->actionStatusActive ||
        !in_array(ucfirst($device['osType']), $this->osType)
        && (
          (
            $device['osType'] === 'Android' &&
            $device['androidAgentVersion'] >= 3960
            //$device['isEmergencyModeActive'], 'true_false', false
            //$device['isRemoteEmergencyModeActive'], 'true_false', false
          )
          ||
          (
            $device['osType'] === 'Windows'
            //$device['isEmergencyModeActive'], 'true_false', false
            //$device['isRemoteEmergencyModeActive'], 'true_false', false
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
