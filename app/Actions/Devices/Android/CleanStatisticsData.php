<?php

namespace App\Actions\Devices\Android;

use App\Actions\Devices\IActionSourceDevices;
use App\Dtos\Controller\ControllerResponse;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use Illuminate\Support\Facades\Log;

class CleanStatisticsData implements IActionSourceDevices
{
  public array $osType = ["Android"];

  private bool $statusActive = true;


  private string $actionType = "CleanStatisticsData";
  public string $actionIdentifier = "CleanStatisticsData_ID";

  /**
   * @param array $device
   * @return array with the attributes keyAttribute = valueAttribute
   */
  public function actionData(array $device): array
  {
    return [
      'actionType' => $this->actionType,
      'actionIdentifier' => $this->actionIdentifier
    ];
  }

  /**
   * @param array $device
   * @param array $license
   * @return bool with the attributes keyAttribute = valueAttribute
   */
  public function checkCompatibility(array $device, array $license): bool
  {
    try {

      if (!in_array('LICENSE_ADVANCED_STANDARD_ACTIVE', $license)) {
        return false;
      }

      if (
        !$this->statusActive ||
        !in_array(ucfirst($device['osType']), $this->osType)
      ) {
        return false;
      }

    } catch (\Exception $exception) {
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    } finally {
      return true;
    }
  }

  /**
   * @param array $device
   * @param string|null $params
   * @return ControllerResponse with the attributes keyAttribute = valueAttribute
   */
  public function execute(array $device, string $params = null): ControllerResponse
  {
    // TODO => send push notification
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
