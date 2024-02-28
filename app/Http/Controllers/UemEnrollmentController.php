<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentCode;
use App\Models\EnrollmentCodeHistory;
use App\Exceptions\CatchedExceptionHandler;
use App\Services\DecryptContent;
use App\Services\DeviceService;
use Illuminate\Support\Facades\DB;
use App\Entities\UemDeviceEntity;
use App\Repositories\UemDeviceRepository;
use App\Entities\UemDeviceDetailsEntity;

/*use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;*/

class UemEnrollmentController extends CrudController
{
    public function check_date_time($params)
    {
        //setDateTimeZone();

        $return = array();

        $oldSignature = false;
        $toEncrypt = false;

        if (isset($params['deviceId']) && (int)$params['deviceId'] > 0) {
            $device = null;
            try {
                //$GLOBALS["sendDbAlerts"]=false;
                $device = response(app(UemDeviceController::class)->getDeviceById($params['deviceId']));;
                //$GLOBALS["sendDbAlerts"]=true;
            } catch (\Exception $e) {
                CatchedExceptionHandler::handle($e);
                return response(["success" => false, "message" => "SERVER_ERROR"], 500);
            }

            if (!is_null($device)) {
                $oldSignature = !isset($params['agentVersion']) || (int)$params['agentVersion'] < 3984;
                $toEncrypt = $device->androidAgentVersion >= 3960 && !$device->isOem;
            }

            define('SIGN_ANDROID_PAYLOADS', 1);

        } else {
            define('SIGN_ANDROID_PAYLOADS', 0); //0 INGORE SIGN, 1 SIGN, 2 DEBUG
        }

        $currentDateTime = date('Y-m-d\TH:i:s\Z');
        $tmp = array("currentDateTime" => $currentDateTime);
        if (isset($params["timeStamp"]) && $params["timeStamp"] > 0) $params["timeStamp"] = (int)$params["timeStamp"];
        $tmp = json_encode($tmp);

        if (!$toEncrypt) {
            $return["currentDateTime"] = date('Y-m-d\TH:i:s\Z');
            $return["timestamp"] = isset($params["timeStamp"]) ? (int)$params["timeStamp"] : 0;
        } else {
            $return["signedContent"] = base64_encode($tmp);
            $return["signature"] = Payload::sign($tmp, OsType::ANDROID, false, $oldSignature);
        }

        return $return;

        /*if(defined("SIGN_ANDROID_PAYLOADS") && SIGN_ANDROID_PAYLOADS===2 ){
          $return["currentDateTime"]=$currentDateTime;
        }

        if(defined("SIGN_ANDROID_PAYLOADS") && SIGN_ANDROID_PAYLOADS===0 ){
          $return["ignoreSign"]=true;
        }*/


    }

    public function getAndroidEnrollment()
    {
        try {
            // Assuming you want to retrieve the value of the row with 'type' = 'AndroidEnterpriseEnroll'
            $androidEnrollment = EnrollmentCode::on("testing_mdm_prova_d3tGk")
                ->where('type', 'AndroidEnterpriseEnroll')
                ->first();

            if ($androidEnrollment) {
                $decodedJson = json_decode($androidEnrollment->value, true);

                $localProvisioning = "it_it";
                $leaveAllSystemAppsEnabledProvisioning = false;
                $activationCode = "DEMOXN2#106-DF7D-BA32";
                $provisionType = 0;

                // Replace values in the decoded JSON
                $decodedJson["android.app.extra.PROVISIONING_LOCALE"] = $localProvisioning;
                $decodedJson["android.app.extra.PROVISIONING_LEAVE_ALL_SYSTEM_APPS_ENABLED"] = $leaveAllSystemAppsEnabledProvisioning;
                $decodedJson["android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE"]["chimpa_activationCode"] = $activationCode;
                $decodedJson["android.app.extra.PROVISIONING_ADMIN_EXTRAS_BUNDLE"]["provisionType"] = $provisionType;

                EnrollmentCodeHistory::on("testing_mdm_prova_d3tGk")->create([
                    'type' => $androidEnrollment->type,
                    'value' => $androidEnrollment->value,
                ]);

                return $decodedJson;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            // Handle exceptions as needed
            CatchedExceptionHandler::handle($e);
            throw $e; // Rethrow the exception after handling if necessary
        }
    }

    public function getStandardEnrollment()
    {
        try {
            // Assuming you want to retrieve the value of the row with 'type' = 'AndroidEnterpriseEnroll'
            $standardEnrollment = EnrollmentCode::on("testing_mdm_prova_d3tGk")
                ->where('type', 'standard')
                ->first();

            EnrollmentCodeHistory::on("testing_mdm_prova_d3tGk")->create([
                'type' => $standardEnrollment->type,
                'value' => $standardEnrollment->value,
            ]);

            return $standardEnrollment->value;
        } catch (\Exception $e) {
            // Handle exceptions as needed
            CatchedExceptionHandler::handle($e);
            throw $e; // Rethrow the exception after handling if necessary
        }
    }

    public function enrollment($params, $encryptedContent)
    {
        try {

            $mdmToken = $params['mdmToken'];
            $agentversion = $params['agentversion'];
            $vendorId = $params['vendorId'];

            $decryptedData = DecryptContent::decryptAgentContent("enroll", "1000", $vendorId, $encryptedContent, false);

            if (isset($vendorId) && !empty($decryptedData)) {
                $res = false;
                $osType = "android";

            }

            $decryptedData = trim($decryptedData, '"');

            $decryptedData = json_decode($decryptedData, true);

            $parentDeviceId = 1;
            $deviceName = $decryptedData['productName'];
            $modelName = $decryptedData['modelDevice'] ?? '';
            $macAddress = $decryptedData['wifiMacAddress'] ?? '';
            $meid = '';
            $osType = 'android';
            $osEdition = $decryptedData['osName'] ?? '';
            $osVersion = $decryptedData['osVersion'] ?? '';
            $udid = '';
            $vendorId = $vendorId;
            $osArchitecture = $decryptedData['cpu'] ?? '';
            $abbinationCode = '';
            $mdmDeviceId = null;
            $manufacturer = $decryptedData['manufacturer'] ?? '';
            $serialNumber = $decryptedData['serialNumber'] ?? '';
            $imei = $decryptedData['imei'] ?? '';
            $isDeleted = 0;
            $phoneNumber = '';
            $isOnline = 0;
            $brand = $decryptedData['brand'] ?? '';
            $networkDetails = $decryptedData['networkInterfaces'] ?? [];
            $deviceIdentity = $decryptedData['$deviceIdentity'] ?? '{}';
            $configuration = $decryptedData['configuration'] ?? '{}';

            /*$configuration = [
              'androidPlayServicesId' => $decryptedData['androidPlayServicesId'] ?? '',
              'bootloader' => $decryptedData['bootloader'] ?? '',
              'brand' => $decryptedData['brand'] ?? '',
              'cpu' => $decryptedData['cpu'] ?? '',
              'manufacturer' => $decryptedData['manufacturer'] ?? '',
              'modelName' => $decryptedData['modelName'] ?? '',
              'securityPatchDate' => $decryptedData['securityPatchDate'] ?? '',
              'hasPlayServices' => $decryptedData['hasPlayServices'] ?? 0,
            ];*/

            /*$deviceIdentity = [
              'simState' => $decryptedData['simState'] ?? '',
              'simOperatorName' => $decryptedData['simOperatorName'] ?? '',
              'cellularNetworkType' => $decryptedData['cellularNetworkType'] ?? '',
              'deviceCapacity' => $decryptedData['deviceCapacity'] ?? '',
              'freeDeviceSpace' => $decryptedData['freeDeviceSpace'] ?? '',
              'externalCapacity' => $decryptedData['externalCapacity'] ?? '',
              'freeExternalSpace' => $decryptedData['freeExternalSpace'] ?? '',
              'hasGoogleAccount' => $decryptedData['hasGoogleAccount'] ?? false,
              'gplayManagedAccountStatus' => $decryptedData['gplayManagedAccountStatus'] ?? false,
              'isBluetoothActive' => $decryptedData['isBluetoothActive'] ?? false,
              'isWifiActive' => $decryptedData['isWifiActive'] ?? false,
              'wifiActiveSSID' => $decryptedData['wifiActiveSSID'] ?? '',
              'wifiBSSID' => $decryptedData['wifiBSSID'] ?? '',
              'isActivationLockEnabled' => $decryptedData['isActivationLockEnabled'] ?? false,
              'isPasscodeCompliant' => $decryptedData['isPasscodeCompliant'] ?? false,
              'isLostModeEnabled' => $decryptedData['isLostModeEnabled'] ?? false,
              'wifiIpAddress' => $decryptedData['wifiIpAddress'] ?? '',
              'wifiLinkSpeed' => $decryptedData['wifiLinkSpeed'] ?? 0,
              'agentVersion' => $decryptedData['agentVersion'] ?? '',
              'isSupervised' => $decryptedData['isSupervised'] ?? false,
              'isEnrolled' => $decryptedData['isEnrolled'] ?? false,
              'isAndroidForWork' => $decryptedData['isAndroidForWork'] ?? false,
              'isAndroidAdmin' => $decryptedData['isAndroidAdmin'] ?? false,
              'batteryLevel' => $decryptedData['batteryLevel'] ?? 0,
              'isBatteryCharging' => $decryptedData['isBatteryCharging'] ?? false,
              'osUpdatePolicy' => $decryptedData['osUpdatePolicy'] ?? ['osUpdatePolicy' => 'NONE'],
              'ram' => $decryptedData['ram'] ?? 0
            ];*/

            $extractedValues = [
                'parentDeviceId' => $parentDeviceId,
                'deviceName' => $deviceName,
                'modelName' => $modelName,
                'macAddress' => $macAddress,
                'meid' => $meid,
                'osType' => $osType,
                'osVersion' => $osVersion,
                'udid' => $udid,
                'vendorId' => $vendorId,
                'osArchitecture' => $osArchitecture,
                'abbinationCode' => $abbinationCode,
                'mdmDeviceId' => $mdmDeviceId,
                'manufacturer' => $manufacturer,
                'serialNumber' => $serialNumber,
                'imei' => $imei,
                'isDeleted' => $isDeleted,
                'phoneNumber' => $phoneNumber,
                'configuration' => $configuration,
                'deviceIdentity' => $deviceIdentity,
                'isOnline' => $isOnline,
                'brand' => $brand,
                'osEdition' => $osEdition,
            ];

            $extractedDetails = [
                'parentDeviceId' => $parentDeviceId,
                'hardwareDetails' => $decryptedData['hardwareProperties'] ?? [],
                'technicalDetails' => [
                    'ram' => $decryptedData['ram'] ?? 0,
                    'tempSensors' => $decryptedData['tempSensors'] ?? [],
                    'isPasscodeEnabled' => $decryptedData['isPasscodeEnabled'] ?? false,
                    'isDeviceLocatorServiceEnabled' => $decryptedData['isDeviceLocatorServiceEnabled'] ?? 0,
                    'hasHardwareGPS' => $decryptedData['hasHardwareGPS'] ?? false,
                    'isEncrypted' => $decryptedData['isEncrypted'] ?? false,
                    'displayResolutionCapability' => $decryptedData['displayResolutionCapability'] ?? '',
                    'orientationSupport' => $decryptedData['orientationSupport'] ?? 0,
                    'isAgentOn' => $decryptedData['isAgentOn'] ?? false,
                    'isEmergencyModeActive' => $decryptedData['isEmergencyModeActive'] ?? false,
                    'isRemoteEmergencyModeActive' => $decryptedData['isRemoteEmergencyModeActive'] ?? false,
                    'isNetworkSpecialPermissionActive' => $decryptedData['isNetworkSpecialPermissionActive'] ?? false,
                    'phoneNumber' => $decryptedData['phoneNumber'] ?? [],
                    'timeOffset' => $decryptedData['timeOffset'] ?? 0,
                ],
                'restrictions' => [
                    'isActivationLockEnabled' => $decryptedData['isActivationLockEnabled'] ?? false,
                    'isPasscodeCompliant' => $decryptedData['isPasscodeCompliant'] ?? false,
                    'isLostModeEnabled' => $decryptedData['isLostModeEnabled'] ?? false,
                    'lockscreenTimeout' => $decryptedData['lockscreenTimeout'] ?? 0,
                ],
                'locationDetails' => [
                    'wifiIpAddress' => $decryptedData['wifiIpAddress'] ?? '',
                    'wifiLinkSpeed' => $decryptedData['wifiLinkSpeed'] ?? 0,
                ],
                'networkDetails' => [
                    'cellularNetworkType' => $decryptedData['cellularNetworkType'] ?? '',
                    'wifiActiveSSID' => $decryptedData['wifiActiveSSID'] ?? '',
                    'wifiBSSID' => $decryptedData['wifiBSSID'] ?? '',
                    'wifiMacAddress' => $decryptedData['wifiMacAddress'] ?? '',
                    'networkInterfaces' => $decryptedData['networkInterfaces'] ?? [],
                ],
                'accountDetails' => [
                    'hasGoogleAccount' => $decryptedData['hasGoogleAccount'] ?? false,
                    'gsuiteUsernameAccount' => $decryptedData['gsuiteUsernameAccount'] ?? '',
                    'gplayManagedAccountStatus' => $decryptedData['gplayManagedAccountStatus'] ?? false,
                    'googleUsernameAccounts' => $decryptedData['googleUsernameAccounts'] ?? [],
                ],
                'osDetails' => [
                    'osVersion' => $decryptedData['osVersion'] ?? '',
                    'osName' => $decryptedData['osName'] ?? '',
                    'buildVersion' => $decryptedData['buildVersion'] ?? '',
                    'securityPatchDate' => $decryptedData['securityPatchDate'] ?? '',
                    'osUpdatePolicy' => $decryptedData['osUpdatePolicy']['osUpdatePolicy'] ?? '',
                ],
                'securityDetails' => [
                    'isRooted' => $decryptedData['isRooted'] ?? false,
                    'signedAttestation' => $decryptedData['signedAttestation'] ?? '',
                    'knoxVersion' => $decryptedData['knoxVersion'] ?? 0,
                    'knoxLicenseActivated' => $decryptedData['knoxLicenseActivated'] ?? '',
                ],
                'androidConfigs' => $decryptedData['androidConfigs'] ?? [],
                'appleConfigs' => $decryptedData['appleConfigs'] ?? [],
                'installedApps' => $decryptedData['installedApps'] ?? [],
                'miscellaneous' => [
                    'batteryLevel' => $decryptedData['batteryLevel'] ?? 0,
                    'isBatteryCharging' => $decryptedData['isBatteryCharging'] ?? false,
                    'hardwareDetails' => $decryptedData['hardwareProperties'] ?? [],
                    'screenLightTimeSinceDate' => $decryptedData['screenLightTimeSinceDate'] ?? '',
                    'screenLightTime' => $decryptedData['screenLightTime'] ?? 0,
                ],
            ];

            $uemDeviceRepository = new UemDeviceRepository();
            $newDeviceEntity = new UemDeviceEntity($extractedValues);
            $newDeviceDetailsEntity = new UemDeviceDetailsEntity($extractedDetails);

            $result = $uemDeviceRepository->storewithDetails($newDeviceEntity, $newDeviceDetailsEntity);

            return $result;
        } catch (\Exception $e) {
            // Handle exceptions as needed
            CatchedExceptionHandler::handle($e);
            throw $e; // Rethrow the exception after handling if necessary
        }
    }

    public function getLogic()
    {
        try {
            // Assuming you want to retrieve the value of the row with 'type' = 'AndroidEnterpriseEnroll'
            $standardEnrollment = EnrollmentCode::on("testing_mdm_prova_d3tGk")
                ->where('type', 'standard')
                ->first();

            EnrollmentCodeHistory::on("testing_mdm_prova_d3tGk")->create([
                'type' => $standardEnrollment->type,
                'value' => $standardEnrollment->value,
            ]);

            return $standardEnrollment->value;
        } catch (\Exception $e) {
            // Handle exceptions as needed
            CatchedExceptionHandler::handle($e);
            throw $e; // Rethrow the exception after handling if necessary
        }
    }

  private function updateAgentMDMInfo($deviceId, $mdmToken, $agentVersion): void
  {
    $deviceService = new DeviceService();
    $deviceService->updateAgentMDMInfo($deviceId, $mdmToken, $agentVersion);
  }

  private function installDefaultAppsAndConfs($deviceId): void
  {

    return;
  }

  public function setupConfirmation($params): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
  {
    $deviceId = null;
    try {
      $deviceId = $params['deviceId'] ?? 0;
      $mdmToken = $params['mdmToken'] ?? null;
      $agentVersion = $params['agentversion'] ?? null;
      $setup = $params['setup'] ?? null;

      if (!$deviceId || $deviceId < 0) {
        throw new Exception('RESTRICTED_ACCESS', 403);
      }

      $this->updateAgentMDMInfo($deviceId, $mdmToken, $agentVersion);

      $deviceService = new DeviceService();
      if ($deviceService->hasGooglePlayServices($deviceId)) {
        $this->installDefaultAppsAndConfs($deviceId);

      }
      // TODO: GUARDARE DTO -> ENTITY + REPOSITORY, ADATTARE IL CODICE

      // TODO: Updates agent MDM (Mobile Device Management) info.
      // TODO: Installs default apps and configurations if the device has Google Play Services.

      return response(['success' => true, 'deviceId' => $deviceId], 200);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      throw $e;
    }
  }
}
