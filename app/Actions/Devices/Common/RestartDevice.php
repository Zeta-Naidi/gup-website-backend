<?php

namespace App\Actions\Devices\Common;

use App\Actions\Devices\IActionSourceDevices;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use App\Dtos\Controller\ControllerResponse;
use Illuminate\Support\Facades\Log;

class RestartDevice implements IActionSourceDevices
{
  public array $osType = ["Android", "Apple", "Windows"];

  private string $actionType = "RestartDevice";
  public string $actionIdentifier = "RestartDevice_ID";
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
    // - check action not disabled ???? ( i dont think its needed )
    // - device osType
    // - device os version
    // - device agent version
    // - ......

    try {
      if (
        !$this->actionStatusActive ||
        !in_array(ucfirst($device['osType']), $this->osType)) {
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
