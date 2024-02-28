<?php

namespace App\Http\Controllers;

use App\Actions\Devices\IActionSourceDevices;
use App\Dtos\Controller\ControllerResponse;
use App\Exceptions\CatchedExceptionHandler;
use App\Repositories\UemDeviceRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;

class ActionController extends Controller
{

  private array $actionsWorkingOnExpiredLicense = ["Unenroll", "UnenrollAndDelete"]; // completare ...

  /**
   * @param int $id
   * @return ControllerResponse with the attributes keyAttribute = valueAttribute
   */
  public function getActionsListUser(int $id): ControllerResponse
  {
    try {
      /**
       * @var UserRepository $repository
       */
      $repository = app(UserRepository::class); // TODO: CHECK
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listItem = $repository->getUserById(id: $id);
      $user = get_object_vars($listItem->formatControllerResponse()->payload);

      if($listItem->formatControllerResponse()->success !== true && !$listItem->isSuccess()){
        return new ControllerResponse(false, null, 500);
      }

      $actions = [];

      $actionDirectory = base_path("app/Actions/Users/");
      $baseNamespace = "App\\Actions\\Users\\";

      foreach (glob($actionDirectory . "*.php") as $actionFilename) {
        require_once $actionFilename;
        $className = pathinfo($actionFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();

        // TODO: controllare che la licenza/e sia/no attiva/e e poi controllare il checkCompatibility
        // check license if license Expired and $class not in $actionsWorkingOnExpiredLicense execute
        // else go out
        $compatibility = $class->checkCompatibility($user, ["license"]);

        if ($compatibility) {
          $action = $class->actionData($user);

          $actions[] = $action;
        }
      }

      return new ControllerResponse(true, $actions, 200);

    } catch (\Exception $exception) {
      CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }

  /**
   * @param int $id
   * @return ControllerResponse with the attributes keyAttribute = valueAttribute
   */
  public function getActionsListDevice(int $id): ControllerResponse
  {
    try {
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listItem = $repository->getDeviceById(id: $id);
      $device = get_object_vars($listItem->formatControllerResponse()->payload);

      if($listItem->formatControllerResponse()->success !== true && !$listItem->isSuccess()){
        return new ControllerResponse(false, null, 500);
      }

      $actions = [];

      // COMMON ACTIONS FOR ALL THE CONFIGURATIONS
      $commonDirectory = base_path('app/Actions/Devices/Common/');
      $baseNamespace = "App\\Actions\\Devices\\Common\\";
      foreach (glob($commonDirectory . "*.php") as $commonFilename) {
        $className = pathinfo($commonFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();

        // TODO: controllare che la licenza/e sia/no attiva/e e poi controllare il checkCompatibility
        // check license if license Expired and $class not in $actionsWorkingOnExpiredLicense execute
        // else go out
        /**
         * @var IActionSourceDevices $class
         */
        $compatibility = $class->checkCompatibility($device, ["license"]);

        if ($compatibility) {
          $action = $class->actionData($device);

          $actions[] = $action;
        }
      }

      $actionDirectory = base_path("app/Actions/Devices/" . ucfirst($device['osType']) . "/");
      $baseNamespace = "App\\Actions\\Devices\\" . ucfirst($device['osType']) . "\\";

      foreach (glob($actionDirectory . "*.php") as $actionFilename) {
        require_once $actionFilename;
        $className = pathinfo($actionFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        /**
         * @var IActionSourceDevices $class
         */
        // TODO: controllare che la licenza/e sia/no attiva/e e poi controllare il checkCompatibility
        // check license if license Expired and $class not in $actionsWorkingOnExpiredLicense execute
        // else go out
        $compatibility = $class->checkCompatibility($device, ["license"]);

        if ($compatibility) {
          $action = $class->actionData($device);

          $actions[] = $action;
        }
      }

      return new ControllerResponse(true, $actions, 200);

    } catch (\Exception $exception) {
      CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }

  /**
   * @param string $actionIdentifier
   * @param int $id
   * @param array|null $actionFormContent $
   * @return ControllerResponse with the attributes keyAttribute = valueAttribute
   */
  public function execute(string $actionIdentifier, int $id, array $actionFormContent = null): ControllerResponse
  {
    try {
      // TODO => differenziare tra Device e User ():

      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listItem = $repository->getDeviceById(id: $id);
      $device = get_object_vars($listItem->formatControllerResponse()->payload);

      if(!$listItem->formatControllerResponse()->success && !$listItem->isSuccess()){
        return new ControllerResponse(false, null, 500);
      }

      $foundClass = null;

      $commonDirectory = base_path('app/Actions/Devices/Common/');
      $baseNamespace = "App\\Actions\\Devices\\Common\\";
      foreach (glob($commonDirectory . "*.php") as $commonFilename) {
        $className = pathinfo($commonFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        if ( $class->actionIdentifier == $actionIdentifier) {
          $foundClass = $class;
        }
      }

      $actionDirectory = base_path("app/Actions/Devices/" . ucfirst($device['osType']) . "/");
      $baseNamespace = "App\\Actions\\Devices\\" . ucfirst($device['osType']) . "\\";

      foreach (glob($actionDirectory . "*.php") as $actionFilename) {
        require_once $actionFilename;
        $className = pathinfo($actionFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        if ( $class->actionIdentifier == $actionIdentifier) {
          $foundClass = $class;
        }
      }

      /**
       * @var IActionSourceDevices $foundClass
       */
      $compatibility = $foundClass->checkCompatibility($device, ["license"]);

      if ($compatibility) {
        $foundClass->execute($device, $this->sanitizeActionFormContent($actionFormContent));
      }

      return new ControllerResponse(true, [], 200);

    } catch (\Exception $exception) {
      CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function sanitizeActionFormContent(array|null $actionFormContent): string|null|ControllerResponse
  {
    try {
      if($actionFormContent === null)
        return null;

      $sanitizedActionFormContent = null;

      foreach ($actionFormContent as $item) {
        $sanitizedActionFormContent[$item['paramId']] = $item['value'];
      }

      return json_encode($sanitizedActionFormContent);

    } catch (\Exception $exception) {
      CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }
}
