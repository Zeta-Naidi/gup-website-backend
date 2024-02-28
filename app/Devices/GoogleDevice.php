<?php

namespace App\Devices;


use App\Repositories\UemDeviceRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class GoogleDevice
 *
 * @property-read int $id
 * @property-read int|null $parentDeviceId
 * @property-read string|null $deviceName
 * @property-read string|null $modelName
 * @property-read int|null $enrollmentType
 * @property-read string|null $macAddress
 * @property-read string|null $meid
 * @property-read string $osType
 * @property-read string $osEdition
 * @property-read string $osVersion
 * @property-read string|null $udid
 * @property-read string|null $vendorId
 * @property-read string|null $osArchitecture
 * @property-read string|null $abbinationCode
 * @property-read int|null $mdmDeviceId
 * @property-read string $manufacturer
 * @property-read string|null $serialNumber
 * @property-read string|null $imei
 * @property-read bool $isDeleted
 * @property-read string|null $phoneNumber
 * @property-read bool $isOnline
 * @property-read string|null $brand
 * @property-read array|null $networkIdentity
 * @property-read array|null $configuration
 * @property-read array|null $deviceIdentity
 * @property-read string|null $createdAt
 */
class GoogleDevice {


  private static string $apiKeyAndroid = "AAAANlXMefw:APA91bGFAqbGMY-bTx54xw5Jxg3SSYw-t4143VC1MMpuiUN8wvqFS1dbzTmJz9GBoHE4STxglwesIJecatESsItTG3xNW1Lq6mifltiCbWb7uD6TwqcgVWMuT5jUkx3BPtviO85HjFxp";
  private static string $apiKey = "AAAAcDdfou0:APA91bFxAA7s4CRzCaAXRfCCqT4ZcqNNP8mAF49KlYfPbvdGDtTLONwiSuojcWqpN0C5wimd6xCvIHTDlK11UhsAkNeIgac-LHmtSqgezTBDcd3skK20B60_Rfm28utaryuHohl2P-SN";
  private static string $url = "https://fcm.googleapis.com/fcm/send";

  /**
   * invia una push notification ad un array di devicetoken
   *
   * @param        $osType
   * @param        $userType
   * @param        $pushType
   * @param        $silentMode
   * @param        $deviceTokens
   * @param        $theMessage
   * @param string $context
   * @param string $action
   * @param bool $multiThread
   * @param bool $external
   * @return void
   */
  public static function sendPushNotification($osType, $userType, $pushType, $silentMode, $deviceTokens, $theMessage, string $context = "", string $action = "", bool $multiThread = true, $external=false): void
  {
    try {
      $attempts = 0;

      if (count($deviceTokens) > 1000) {
        for ($i = 0; $i < ceil(count($deviceTokens) / 1000); $i++) {
          $offset = 1000 * $i;
          $splittedDeviceTokens = array_slice($deviceTokens, $offset, 1000, true);
          self::sendPushNotification($osType, $userType, $pushType, $silentMode, $splittedDeviceTokens, $theMessage, $context, $action, $multiThread, $external);
        }
      } elseif (!empty($deviceTokens)) {
        $body = self::_createPushBody($pushType, $silentMode, $theMessage, $context, $action);
        $retryWaitTime = 1; //PUSH_RETRY_WAIT_TIME;
        $retryTimes = 7; // PUSH_RETRY_TIMES;
        $data = $body[0];
        $notification = $body[1];

        $api_key = ($osType == "android") ? self::$apiKeyAndroid : self::$apiKey;

        while (true) {
          $attempts++;

          $retry = [];
          $fields = [
            "registration_ids" => $deviceTokens,
            'data' => $data
          ];

          if (!is_null($notification)) {
            $fields['notification'] = $notification;
          }

          if ($osType == "ios") { //OsType::IOS
            $fields['content_available'] = true;
          }

          if ($osType == "android" && $context != "" && $pushType == "MDM") { //OsType::ANDROID,  MDM_PUSH
            $fields['collapse_key'] = $context;
          }

          if ($pushType == "ALERT") { // ALERT_PUSH
            $fields['time_to_live'] = 1800;
            $fields['priority'] = "high";
          } elseif ($pushType == "SERVICE") { // SERVICE_PUSH
            $fields['time_to_live'] = 2400;
            $fields['priority'] = "high";
          } elseif ($pushType == "MDM") { // MDM_PUSH
            if ($attempts >= 6) {
              $retryWaitTime = 90;
            }
            $fields['priority'] = "high";
          }

          $headers = [
            'Authorization' => 'key=' . $api_key,
            'Content-Type' => 'application/json; charset=utf-8'
          ];

          $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post(self::$url, $fields);

          if ($response->status() == 400) {
            // Handle 400 Bad Request if needed
            LOG::info("ERRORE");
            LOG::info(json_encode($response));
          }
          else {
            $results = $response->json();
            LOG::info(json_encode($results));

            if (!$external && ($pushType !== "ALERT" || ($context !== "" && !is_null($context))))
            {
              // TODO:
              // fa l'update del 'pushDeviceToken', nella tabella 'tblBindings', dove
              // il 'pushDeviceToken' è uguale a 1 o più 'pushDeviceToken'

              /*if (false === true) { // TODO LICENSE_ADVANCED_EDUBASE_ACTIVE
                $query = 'UPDATE tblBindings SET pushDeviceToken=NULL WHERE pushDeviceToken IN (';
              }

              $queryAdd = "";
              $h = true;
              */
              $removeEmm = [];

              $devicesTokensToUpdate = [];

              foreach ($results['results'] as $key => $result) {
                if (isset($result['error'])) {
                  if ($result['error'] == 'InvalidRegistration' || $result['error'] == 'NotRegistered') {
                    if (!is_null($deviceTokens[$key]) && isset($query) && $pushType !== "MDM") { // MDM_PUSH

                      $devicesTokensToUpdate[] = $deviceTokens[$key];

                      /*if ($h) {
                        $queryAdd = '"' . $deviceTokens[$key] . '"';
                        $h = false;
                      } else {
                        $queryAdd = $queryAdd . ', "' . $deviceTokens[$key] . '" ';
                      }*/
                    }
                  } else {
                    if ($result['error'] == "Unavailable" || $result['error'] == "InternalServerError") {
                      $retry[] = $deviceTokens[$key];
                    }

                    if ($result['error'] == "InvalidPackageName" || $result['error'] == "MessageTooBig" || $result['error'] == "DeviceMessageRateExceeded") {
                      // Handle specific error cases

                      if (!is_null($deviceTokens[$key]) && $pushType == "MDM" && $osType == "android" && false === true) { // MDM_PUSH, OsType::ANDROID, GOOGLE_EMM_CONFIGURED

                        /**
                         * @var UemDeviceRepository $repository
                         */
                        /*$repository = app(UemDeviceRepository::class);
                        $repository->setConnectionToDatabase("testing_mdm_prova_d3tGk"); // TODO: auth()->user()->nameDatabaseConnection
                        $deviceIdTmp = $repository->getDeviceIdFromMdmPushDeviceToken($deviceTokens[$key])->formatControllerResponse();

                        if ($deviceIdTmp > 0) {
                          $removeEmm[] = $deviceIdTmp;
                        }*/
                      }
                    }
                  }
                }
              }

              /**
               * @var UemDeviceRepository $repository
               */
              /*$repository = app(UemDeviceRepository::class);
              $repository->setConnectionToDatabase("testing_mdm_prova_d3tGk"); // TODO: auth()->user()->nameDatabaseConnection
              $updated = $repository->updateTblBindings($devicesTokensToUpdate)->formatControllerResponse();
              /*if(!$updated->success){

              }*/

              if (!empty($removeEmm)) {
                foreach ($removeEmm as $singleDeviceId) {
                  self::removeAndroidSupervision($singleDeviceId);
                }
              }

              if (is_array($retry) && !empty($retry) && $attempts <= $retryTimes) {
                unset($deviceTokens);
                $deviceTokens = $retry;

                $retryWaitTimeDef = $retryWaitTime * $attempts;

                sleep($retryWaitTimeDef);

                continue; // Continue the loop for retry
              }
            }
          }

          break; // Break the loop when done
        }
      }
    } catch (\Exception $exception) {
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    } finally {
      if ($multiThread) {
        exit;
      }
    }
  }

  /**
   * @param $type
   * @param $silentMode
   * @param $theMessage
   * @param $context
   * @param $action
   * @return array
   */
  private static function _createPushBody($type, $silentMode, $theMessage, $context, $action): array
  {
    try {
      $body[0]['type'] = $type;
      if (!$silentMode) {
        $body[1]['sound'] = 'default';
        $body[1]['badge'] = 1;
        if ($theMessage != "") {
          $body[1]['body'] = $theMessage;
        }
      } else {
        if ($theMessage != "") $body[0]['bodySilent'] = $theMessage;
      }
      if ($action != "") $body[0]['action'] = $action;
      if ($context != "") $body[0]['context'] = $context;
      return $body;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return [];
    }
  }

  /**
   * remove a device from Android Supervision, give his id
   *
   * @param $deviceId
   * @return void
   */
  private static function removeAndroidSupervision($deviceId): void
  {
    try {
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $update = $repository->removeAndroidSupervision($deviceId)->formatControllerResponse();

      /*
       * TODO:
       * questo addcheckoutentry va tenuto dopo la query principale
      Action::addCheckoutEntry($deviceId, NULL, DeliveryStatus::DELIVERED, array("keepDeviceOwner" => !$removeDeviceOwnerPermission));

      if ((defined("LICENSE_ADVANCED_EDU_ACTIVE") && LICENSE_ADVANCED_EDU_ACTIVE === true) || (defined("LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE") && LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE === true)) {

        $childDeviceIds = Device::getChildDeviceIdsCope($deviceId, false, false, true);

        if (!empty($childDeviceIds)) {
          foreach ($childDeviceIds as &$childDeviceId) {
            if ($childDeviceId > 0) {
              Device::delete($childDeviceId, USER_ID_SYSTEM, true, false, false);
            }
          }
        }
      }

      self::unbind($deviceId);
      if ($return && GOOGLE_EMM_CONFIGURED) {

        //$GoogleEmmService = new GoogleEmmService();
        $gplayAccount = self::getEmmGplayAccount($deviceId);
        if (!empty($gplayAccount)) {
          $return = GoogleEmmService::clearAllManagedConfigurations($gplayAccount['gplayAccountId'], $gplayAccount['androidPlayServicesId'], $deviceId);
          if ($GoogleEmmService->enterpriseType == GoogleEmmServiceEnterpriseType::GOOGLE_DOMAIN)
            $return = GoogleEmmUtility::disableGsuiteGplayDevice($gplayAccount['androidPlayServicesId'], $gplayAccount['gplayAccountId']);
        }
      }*/

    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
    }
  }



}

