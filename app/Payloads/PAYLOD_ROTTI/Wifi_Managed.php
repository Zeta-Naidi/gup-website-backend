<?php

namespace App\Payloads\Android;

use App\Models\Payload;
use App\Models\UemDevice;

/*enum OsType: string
{
  case IOS = 'iOS';
  case ANDROID = 'android';
  case WINDOWS = 'windows';
  case MACOS = 'macosx';
}

enum DeviceModel: string
{
  case IPAD = 'ipad';
  case IPHONE = 'iphone';
  case IPOD = 'ipod';
  case APPLETV = 'appletv';
}

enum AndroidOsUserType: string
{
  case OS_USER_ACCOUNT = 'user_account';
}

enum PayloadType: string
{
  case CERTIFICATE_PEM = 'certificate_pem';
  case CERTIFICATE_PKCS1 = 'certificate_pkcs1';
  case CERTIFICATE_PKCS12 = 'certificate_pkcs12';
}*/

const LICENSE_ADVANCED_EDU_ACTIVE = "Edu";
const LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE = false;

class Wifi_Managed extends Payload // network?
{
  protected $SSID_STR;
  protected $encryption;
  protected $proxy;
  protected $network;
  protected $HIDDEN_NETWORK;
  protected $autoJoin;
  protected $password;
  protected $captiveBypass;
  protected $disableAssociationMACRandomization;
  protected $qoSMarkingPolicy;
  protected $grantKeyPairToWifiAuth;

  /*protected $AndroidCommandType = "CommandType::WIFI";
  protected $PayloadType = "CommandType::WIFI";
  protected $ApplePayloadType = "CommandType::WIFI";*/

  public function checkCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
  {
    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;


    if (!is_null($device)) {
      //niente
    } elseif (!is_null($deviceIdentifier)) {
      if (is_null($GLOBALS["device"])) {
        $GLOBALS["device"] = new UemDevice($deviceIdentifier);
      }
      $device = &$GLOBALS["device"];
    } elseif (!is_null($specialOsType)) {
      //niente
    } else {
      throw new Exception("all args are null");
    }

    if ($device->osType == OsType::ANDROID || $specialOsType == OsType::ANDROID) {
      if (!is_null($device) && $device->spaceType === AndroidOsUserType::OS_USER_ACCOUNT) {
        $return = false;
      } elseif (!is_null($device) && $device->androidAgentVersion > 30) {
        $return = true;
      } elseif ($specialOsType == OsType::ANDROID) {
        $return = true;
      }
    } elseif ($device->osType == OsType::IOS || $specialOsType == OsType::IOS) {
      if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD || $device->modelName == DeviceModel::APPLETV) {
        $return = true;
      } elseif ($specialOsType == OsType::IOS) {
        $return = true;
      }
    } elseif ($device->osType == OsType::WINDOWS || $specialOsType == OsType::WINDOWS) {
      $return = false;
    }
    return $return;
  }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WIFI_MANAGED_WINDOWS.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Proxy",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    // TODO: RICONTROLLARE TUTTI I CAMPI
    $schema = [];

    if($osType === 'Mixed'){
      return $schema;
    }else{
      if (in_array($osType, $this->availableOs)) {
        $filteredSchema = array_filter($schema, function ($item) use ($osType) {
          return (strtolower($item['os']) === strtolower($osType) || strtolower($item['os']) === 'mixed');
        });
        // Reindex the array to reset the keys
        return array_values($filteredSchema);
      }else{
        return [];
        // throw \Exception::class
      }
    }
  }
}
