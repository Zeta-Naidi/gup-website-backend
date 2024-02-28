<?php

namespace App\Actions\Devices\Android;

use App\Actions\Devices\IActionSourceDevices;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use App\Dtos\Controller\ControllerResponse;
use Illuminate\Support\Facades\Log;

class EnableFactoryResetProtection implements IActionSourceDevices
{
  public array $osType = ["Android"];

  private bool $actionStatusActive = true;


  private string $actionType = "EnableFactoryResetProtection";
  public string $actionIdentifier = "EnableFactoryResetProtection_ID";

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

      if (!in_array('LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE', $license)) {
        return false;
      }

      if (
        !$this->actionStatusActive &&
        !in_array(ucfirst($device['osType']), $this->osType)
        && (
          (
            $device['isEnrolled'] === 1 &&
            $device['isSupervised'] === 1 &&
            $device['assignedLicense'] > 0 &&
            $device['androidAgentVersion'] > 30
            // 'hasAndroidPlayServices', '=', 1
            // 'isOem', '=', 0
            // 'isActivationLockEnabled', 'true_false', false
          )
          ||
          (
            $device['isEnrolled'] === 1 &&
            $device['isEnhancedWorkProfile'] === 1 &&
            $device['assignedLicense'] > 0 &&
            $device['androidAgentVersion'] > 30
            // 'hasAndroidPlayServices', '=', 1
            // 'isOem', '=', 0
            // 'isActivationLockEnabled', 'true_false', false
          )
        )
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
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }
}
