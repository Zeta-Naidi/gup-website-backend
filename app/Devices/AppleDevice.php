<?php

namespace App\Devices;

use App\Repositories\ActionRepository;
use App\Repositories\UemDeviceRepository;
use Illuminate\Support\Facades\Http;
use mysql_xdevapi\Exception;

/**
 * Class AppleDevice
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
class AppleDevice {


  private static string $urlDevMode = "https://api.sandbox.push.apple.com/3/device/";
  private static string $url = "https://api.push.apple.com/3/device/";
  private static string $TEAMID = "KN667CJCA4";
  private static string $KEYID = "SGWWPHZL4G";

  /**
   * send the push notification
   * @param string $identifier
   * @param null $pushContent
   * @param bool $isAgent
   * @param string $notificationType
   * @param int $notificationPriority
   * @param string $topicSuffix
   * @return bool
   */
  public function sendPushNotification(string $identifier, $pushContent = null, bool $isAgent = false, string $notificationType = 'mdm', int $notificationPriority = 10, string $topicSuffix = ''): bool
  {
    try {
      if (is_null($pushContent)) {
        $result = $this->getMdmPushPayload($identifier);

        if (is_null($result["theDeviceToken"])){
          return true;
        }else{
          $theDeviceToken = $result["theDeviceToken"];
        }
        $pushContent = $result["pushContent"];
      }

      if($isAgent) {
        //agent notifications
        return self::sendApnsPushToSingleDeviceWithBearerToken($theDeviceToken ?? $identifier, $pushContent, $notificationType, $notificationPriority, $topicSuffix);
      }

      //os mdm notifications
      return self::sendToSingleDevice($theDeviceToken ?? $identifier, $pushContent, $notificationType, $notificationPriority);
    }catch (\Exception $exception){
    \App\Exceptions\CatchedExceptionHandler::handle($exception);
    return false;
    }
  }

  /**
   * get the mdm push payload
   * @param $identifier
   * @return array
   */
  private static function getMdmPushPayload($identifier): array
  {
    try {
      if (is_string($identifier)) {
        /**
         * @var UemDeviceRepository $repository
         */
        $repository = app(UemDeviceRepository::class);
        $repository->setConnectionToDatabase("testing_mdm_prova_d3tGk"); // TODO: auth()->user()->nameDatabaseConnection
        $result = $repository->getMdmPush($identifier, true)->formatControllerResponse();

      } elseif ($identifier > 0) {
        /**
         * @var UemDeviceRepository $repository
         */
        $repository = app(UemDeviceRepository::class);
        $repository->setConnectionToDatabase("testing_mdm_prova_d3tGk"); // TODO: auth()->user()->nameDatabaseConnection
        $result = $repository->getMdmPush($identifier, false)->formatControllerResponse();
      }else{
        $result = null;
      }

      //check
      $return = [];
      if (!is_null($result[0][0]) && is_array($result) && strlen($result[0][0]) > 0) {
        if (!is_null($result[0][1]))
          $return["theDeviceToken"] = $result[0][1];
        else
          $return["theDeviceToken"] = $identifier;

        $return["pushContent"] = json_encode(array('mdm' => $result[0][0]));
      }

      // return "theDeviceToken", "pushContent"

      return $return;
    }catch (\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return [];
    }
  }

  /**
   * Used to send APNS push to Agent
   * @param string $deviceToken
   * @param string $payload
   * @param string $notificationType
   * @param int $notificationPriority
   * @param string $topicSuffix
   * @return bool
   */
  public static function sendApnsPushToSingleDeviceWithBearerToken(string $deviceToken, string $payload,string $notificationType = 'mdm', int $notificationPriority = 10, string $topicSuffix = ''):bool{
   /*
   if (!file_exists(CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES .  "/AuthKey_".ApnsIds::KEYID.".p8")) {
      copy(
            CERTS_PATH . "/apple_agent/AuthKey_".ApnsIds::KEYID.".p8",
            CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES . "/AuthKey_".ApnsIds::KEYID.".p8"
          );
    }
    $privateKey = file_get_contents(CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES .  "/AuthKey_".ApnsIds::KEYID.".p8");

    */

    try {
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $deviceId = $repository->getDeviceIdFromMdmPushDeviceToken($deviceToken)->formatControllerResponse()->payload;
      if (!$deviceId->success) {
        return false;
      }

      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $update = $repository->updateAppleCheckins($deviceId)->formatControllerResponse();
      if (!$update->success) {
        return false;
      }

      $topic = "eu.chimpa.mdmagents".$topicSuffix; // ChimpaAppBundleId::CHIMPA_MDM_AGENT_IOS.$topicSuffix;
      $url = /* check if DEV_MODE */ self::$url. str_replace(' ', '', $deviceToken); //TODO: (DEV_MODE) ? "https://api.sandbox.push.apple.com/3/device/" :  "https://api.push.apple.com/3/device/";

      $response = Http::withHeaders([
        'apns-priority' => $notificationPriority,
        'apns-push-type' => $notificationType,
        'apns-topic' => $topic,
        'Authorization' => "Bearer ".self::getApnsJwtToken(self::$TEAMID,self::$KEYID, "privateKey"), //TODO: file_get_contents(CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES .  "/AuthKey_".ApnsIds::KEYID.".p8");
        'apns-expiration' => 172800,
      ])
        ->withOptions([
          'http_version' => 4, // CURL_HTTP_VERSION_2TLS,
          'timeout' => 15,
        ])
        ->post($url, $payload);

      if ($response->successful()) {
        return false;
      }

      $statusCode = $response->status();
      $errorResponse = $response->json();

      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $deviceId = $repository->getDeviceIdFromMdmPushDeviceToken($deviceToken)->formatControllerResponse()->payload;
      if (!$deviceId->success) {
        return false;
      }

      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $update = $repository->updateAppleCheckins($deviceId)->formatControllerResponse();
      if (!$update->success) {
        return false;
      }

      /*
       * TODO: $query1 = 'UPDATE IGNORE tblProfileStatus
                      SET lastPushSent=CURRENT_TIMESTAMP , pushAttempts=pushAttempts+1
                      WHERE iosMdm=1 AND deviceId =' . $deviceId . ' AND status=0;';*/

      return true;
    } catch (\Exception $exception) {
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * send push notification HTTP2.0 version
   * @param String $theDeviceToken
   * @param string $payload
   * @param string $notificationType
   * @param int $notificationPriority
   * @return bool
   */
  public static function sendToSingleDevice(String $theDeviceToken, String $payload, string $notificationType = 'mdm', int $notificationPriority = 10)  : bool
  {
    // if cert not found return a 500 error
    /*
   * if(defined('APPLE_MDM_PUSH_CERT_CONFIGURED') && APPLE_MDM_PUSH_CERT_CONFIGURED && file_exists(CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES . "/appleMdmPushCert.pem"))
    {
      $certificate = CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES . "/appleMdmPushCert.pem";
    }
    else
    {
      return false;
    }*/

    try {
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $deviceId = $repository->getDeviceIdFromMdmPushDeviceToken($theDeviceToken)->formatControllerResponse()->payload;
      if (!$deviceId->success) {
        return false;
      }

      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $update = $repository->updateAppleCheckins($deviceId)->formatControllerResponse();
      if (!$update->success) {
        return false;
      }

      $topic = ""; // TODO: APPLE_MDM_PUSH_TOPIC => define('APPLE_MDM_PUSH_TOPIC', $config['mdmApplePushTopic']);
      $url = self::$url. str_replace(' ', '', $theDeviceToken);

      $response = Http::withHeaders([
        'apns-priority' => $notificationPriority,
        'apns-push-type' => $notificationType,
        'apns-topic' => $topic,
      ])->withOptions([
          'http_version' => 4, // CURL_HTTP_VERSION_2TLS,
          'ssl_verify_peer' => false,
          'timeout' => 15,
          'ssl_cert' => '' // $certificate, TODO: CACHE_RAM_PATH.CHIMPSKY_ALIAS_FOLDER_NAME . "." . HOST_PUBLIC_FILES . "/appleMdmPushCert.pem";
        ])
        ->post($url, $payload);

      if (!$response->successful()) {
        return false;
      }

      $statusCode = $response->status();
      $errorResponse = $response->json();

      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $update = $repository->updateAppleCheckins($deviceId)->formatControllerResponse();
      if (!$update->success) {
        return false;
      }

      /*$query1 = 'UPDATE IGNORE tblProfileStatus
                      SET lastPushSent=CURRENT_TIMESTAMP , pushAttempts=pushAttempts+1
                      WHERE iosMdm=1 AND deviceId =' . $deviceId . ' AND status=0;';*/

      return true;
    } catch (\Exception $exception) {
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * https://developer.apple.com/documentation/corelocation/creating_a_location_push_service_extension
   * @param string $teamId
   * @param string $keyId
   * @param string $pemPrivateKey
   * @return mixed|string
   */
  public static function getApnsJwtToken(string $teamId, string $keyId, string $pemPrivateKey): mixed
  {
    try{
      // TODO: get apns_token from the cache
      $tokenJson = Cache::cacheResponse('apns_token',"",-1,true,true,false,true);

      if(!empty($tokenJson) && self::isjson($tokenJson)){
        $tokenArray = json_decode($tokenJson,true);
        $issuedAt = (int)$tokenArray['issuedAt'];
        if($issuedAt > time() - 45 * 60){ // following Apple documentation we need to refresh the token AT LEAST every hour and no more than once every 20 minutes, we decided to perform the refresh every 45 minutes
          return $tokenArray['token'];
        }
      }
      // Cache::cacheDelete('apns_token',true,true,false,true);
      $issuedAt = time();
      // Cache::cacheResponse('apns_token',json_encode(['token' => $token,'issuedAt' => $issuedAt]),60*60,true,true,false,true); //cache the token

      return self::createApnsJwtToken($teamId,$keyId,$pemPrivateKey,$issuedAt);
    }catch(\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * @param string $teamId the value for which is the 10-character Team ID you use for developing your company’s apps
   * @param string $keyId The 10-character Key ID you obtained from your developer account
   * @param string $pemPrivateKey
   * @param int $issuedAt unix epoch in seconds
   * @return string
   */
  private static function createApnsJwtToken(string $teamId,string $keyId,string $pemPrivateKey,int $issuedAt):string{
    try{
      if (! function_exists('openssl_get_md_methods') || ! in_array('sha256', openssl_get_md_methods())) {
        throw new Exception('Requires openssl with sha256 support');
      }

      $privateKey = openssl_pkey_get_private($pemPrivateKey);
      if (!$privateKey){
        throw new Exception('Cannot decode private key... crateApnsJwtToken');
      }

      $msg = self::base64url_encode(json_encode([ 'alg' => 'ES256', 'kid' => $keyId ])) . '.' . self::base64url_encode(json_encode([ 'iss' => $teamId, 'iat' => $issuedAt ]));
      openssl_sign($msg, $der, $privateKey, 'sha256');

      // DER unpacking from https://github.com/firebase/php-jwt
      $components = [];
      $pos = 0;
      $size = strlen($der);
      while ($pos < $size) {
        $constructed = (ord($der[$pos]) >> 5) & 0x01;
        $type = ord($der[$pos++]) & 0x1f;
        $len = ord($der[$pos++]);
        if ($len & 0x80) {
          $n = $len & 0x1f;
          $len = 0;
          while ($n-- && $pos < $size) $len = ($len << 8) | ord($der[$pos++]);
        }

        if ($type == 0x03) {
          $pos++;
          $components[] = substr($der, $pos, $len - 1);
          $pos += $len - 1;
        } else if (! $constructed) {
          $components[] = substr($der, $pos, $len);
          $pos += $len;
        }
      }
      foreach ($components as &$c) $c = str_pad(ltrim($c, "\x00"), 32, "\x00", STR_PAD_LEFT);

      return $msg . '.' . self::base64url_encode(implode('', $components));
    }catch(\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }

  /**
   * check json string
   * @param $string
   * @return bool
   */
  private static function isjson($string): bool
  {
    return is_string($string) && is_array(json_decode($string, true)) && json_last_error() == JSON_ERROR_NONE;
  }

  /**
   * Encode data to Base64URL
   * @param string $data
   * @return boolean|string
   */
  public static function base64url_encode(string $data): bool|string
  {
    try{
      // First of all you should encode $data to Base64 string
      $b64 = base64_encode($data);

      // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
      $url = strtr($b64, '+/', '-_');

      // Remove padding character from the end of line and return the Base64URL result
      return rtrim($url, '=');
    }catch(\Exception $exception){
      \App\Exceptions\CatchedExceptionHandler::handle($exception);
      return false;
    }
  }
}
