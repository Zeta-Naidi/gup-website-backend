<?php

namespace App\Payloads\Android;

use App\Models\Payload;
use App\Models\UemDevice;
use App\Exception;

/*enum PayloadType: string
{
  case VPN = 'VPN';
}

enum ApplePayloadType: string
{
  case VPN = 'VPN';
}

enum DeviceModel: string
{
  case IPAD = 'ipad';
  case IPHONE = 'iphone';
  case IPOD = 'ipod';
  case APPLETV = 'appletv';
}*/

class VPN_Managed extends Payload
{
  const ADULT_FILTER = 'adultFilter';
  const SITES_WEB_FILTER = 'websiteOnly';
  const THIRD_PARTY_APP_FILTER = 'thirdPartyApp';

  protected $VPNType;
  protected $vpnConfig;
  protected $VPNSubType;
  protected $vendorConfig;
  protected $userDefinedName;
  protected $onDemandEnabled;
  protected $onDemandRules;
  protected $overridePrimary;
  protected $providerBundleIdentifier;
  protected $proxy;
  protected $excludeLocalNetworks;
  protected $includeAllNetworks;
  protected $providerDesignatedRequirement;
  protected $enforceRoutes;
  protected $profileXML;
  protected $androidCommandType = NULL;
  /*protected $payloadType = PayloadType::VPN;
  protected $applePayloadType = ApplePayloadType::VPN;*/

  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "ios") {
      $return = true;
    }

    return $return;
  }

  public function checkDeviceCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
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
      throw new \Exception("all args are null");
    }

    if ($device->osType == OsType::ANDROID || $specialOsType == OsType::ANDROID) {
      $return = true;
    } elseif (($device->osType == OsType::IOS && $device->isSupervised === 1) || $specialOsType == OsType::IOS) {
      if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD) {
        $return = true;
      } elseif ($specialOsType == OsType::IOS) {
        $return = true;
      }
    }

    return $return;
  }

  public function getIcon()
  {
    $iconBasePath = "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/VPN.png";
    return $iconBasePath;
  }

  public function getConfig()
  {
    return [
      "show" => true,
      "title" => "VPN",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema()
  {
    return [

      [
        "id" => 1,
        "label" => "Nome",
        "field_id" => "Field id: (UserDefinedName)",
        "value" => "",
        "input_type" => "text",
        "description" => "Nome della configurazione mostrata sul dispositivo",
        "options" => []
      ],
      [
        "id" => 2,
        "label" => "XML Schema per il provisioning di tutti i campi di una VPN",
        "field_id" => "Field id: (ProfileXML)",
        "value" => "",
        "input_type" => "file",
        "description" => "link allo schema dell'xml: https://docs.microsoft.com/it-it/windows/client-management/mdm/vpnv2-profile-xsd",
        "options" => []
      ]

    ];
  }
}
