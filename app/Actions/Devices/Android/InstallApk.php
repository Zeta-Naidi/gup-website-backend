<?php

namespace App\Actions\Devices\Android;

use App\Actions\Devices\IActionSourceDevices;
use App\Entities\ActionEntity;
use App\Repositories\ActionRepository;
use App\Dtos\Controller\ControllerResponse;
use Illuminate\Support\Facades\Log;

class InstallApk implements IActionSourceDevices
{
  public array $osType = ["Android"];

  private bool $actionStatusActive = true;


  private string $actionType = "InstallApk";
  public string $actionIdentifier = "InstallApk_ID";

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

      if (!in_array('LICENSE_ADVANCED_MAM_ACTIVE', $license)) {
        return false;
      }

      if (
        !$this->actionStatusActive &&
        !in_array(ucfirst($device['osType']), $this->osType) &&
        (
          $device['androidAgentVersion'] > 30
          // &&
          // 'osVersionExp0','>=',6 &&
          // 'spaceType','!=',\AndroidOsUserType::OS_USER_ACCOUNT
          // OR
          // 'androidAgentVersion','>',3539
          //'osVersionExp0','>=',5
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
