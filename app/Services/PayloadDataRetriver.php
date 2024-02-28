<?php

namespace App\Services;

use App\Dtos\Controller\ControllerResponse;
use App\Exceptions\CatchedExceptionHandler;

class PayloadDataRetriver
{

  public function getData(string $osType) : ControllerResponse
  {
    try {
      // TODO: ordinamento dei payloads (adesso è in base all'ordine di lettura dei file)
      // TODO: gestire $osType: Mixed, witch payloads to show (now only shows the common) ???

      $payloads = [];

      // COMMON PAYLOADS FOR ALL THE CONFIGURATIONS
      $commonDirectory = base_path('app/Payloads/Common/');
      $baseNamespace = "App\\Payloads\\Common\\";
      foreach (glob($commonDirectory . "*.php") as $commonFilename) {
        $className = pathinfo($commonFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        $compatibility = in_array($osType,$class->availableOs);

        if ($compatibility) {
          $payload = [
            "PayloadName" => $className,
            "icon" => $class->getIcon(),
            "config" => $class->getConfig(),
            "Fields" => $class->getSchema($osType),
            "osCategorized" => true
          ];


          // If "PayloadName" is "General", insert it at the beginning of the array
          if ($className === 'General') {
            array_unshift($payloads, $payload);
          } else {
            $payloads[] = $payload;
          }
        }
      }


      $payloadDirectory = base_path("../app/Payloads/" . ucfirst($osType) . "/");
      $baseNamespace = "App\\Payloads\\" . ucfirst($osType) . "\\";

      foreach (glob($payloadDirectory . "*.php") as $payloadFilename) {
        require_once $payloadFilename;
        $className = pathinfo($payloadFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        $compatibility = in_array($osType,$class->availableOs);

        if ($compatibility) {
          $payload = [
            "PayloadName" => $className,
            "icon" => $class->getIcon(),
            "config" => $class->getConfig(),
            "Fields" => $class->getSchema($osType),
            "osCategorized" => true
          ];

          $payloads[] = $payload;
        }
      }

      return new ControllerResponse(true, $payloads, 200);

    } catch (\Exception $exception) {
      CatchedExceptionHandler::handle($exception);
      return new ControllerResponse(false, null, 500);
    }
  }

}
