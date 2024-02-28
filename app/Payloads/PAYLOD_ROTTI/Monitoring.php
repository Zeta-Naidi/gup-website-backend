<?php

namespace App\Payloads\PAYLOD_ROTTI;

use App\Models\Payload;
use App\Payloads\Common\Device;
use App\Payloads\Common\DeviceModel;
use App\Payloads\Common\Exception;
use App\Payloads\Common\OsType;

class Monitoring extends Payload
{
  const ADULT_FILTER = 'adultFilter';
  const SITES_WEB_FILTER = 'websiteOnly';
  const THIRD_PARTY_APP_FILTER = 'thirdPartyApp';

  protected $type;
  protected $blacklistedURLs;
  protected $whitelistedBookmarks;
  protected $permittedURLs;
  protected $filterBrowsers;
  protected $filterSockets;
  protected $pluginBundleID;
  protected $organization;
  protected $username;
  protected $password;
  protected $userDefinedName;
  protected $serverAddress;
  protected $vendorConfig;
  protected $PayloadCertificateUUID;

  protected $AndroidCommandType = "CommandType::MONITORING";
  protected $PayloadType = "PayloadType::MONITORING";
  protected $ApplePayloadType = "ApplePayloadType::MONITORING";

  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "mac") {
      $return = true;
    }

    return $return;

  }

  public function checkDeviceCompatibility($deviceIdentifier = NULL, Device $device = NULL, string $specialOsType = NULL): bool
  {
    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if (!is_null($device)) {
      //niente
    } elseif (!is_null($deviceIdentifier)) {
      if (is_null($GLOBALS["device"])) {
        $GLOBALS["device"] = new Device($deviceIdentifier);
      }
      $device =& $GLOBALS["device"];
    } elseif (!is_null($specialOsType)) {
      //niente
    } else {
      throw new Exception("all args are null");
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

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/MONITORING.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "MONITORING",
      "description" => "MONITORING_DESCRIPTION",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/MONITORING.png",
    ];
  }

  public function getSchema(): array
  {
    return [
      [
        "id" => 1,
        "os" => "Windows",
        "label" => "Mantenere il sistema operativo aggiornato",
        "field_id" => "Field id: (keepOsUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => "Windows",
        "label" => "Mantenere aggiornate le app gestite tramite l'azione installa applicazione",
        "field_id" => "Field id: (keepAppUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 3,
        "os" => "Apple",
        "label" => "Mantenere il sistema operativo aggiornato",
        "field_id" => "Field id: (keepOsUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 4,
        "os" => "Apple",
        "label" => "Mantenere aggiornate le app gestite tramite l'azione installa applicazione",
        "field_id" => "Field id: (keepAppUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
    ];
  }

}
