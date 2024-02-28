<?php

namespace App\Payloads\Common;

use App\Models\Payload;
use Illuminate\Support\Facades\Log;

class WebContent_Filter extends Payload
{

  public array $availableOs = ['Mixed', 'Android', 'Apple'];

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

  protected $AndroidCommandType = "CommandType::WEB_FILTER";
  protected $PayloadType = "PayloadType::WEB_CONTENT_FILTE2";
  protected $ApplePayloadType = "ApplePayloadType::WEB_CONTENT_FILTER";

  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "ios" ) {
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
      $device = &$GLOBALS["device"];
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
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WEB_CONTENT_FILTER.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "WEB_CONTENT_FILTER",
      "description" => "WEB_CONTENT_FILTER_DESCRIPTION",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WEB_CONTENT_FILTER.png",
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ["Mixed", "Android"],
        "label" => "Allowlist URL",
        "field_id" => "Field id: (permittedURLs)",
        "value" => "",
        "input_type" => "todo",
        "description" => "URL specifici che verranno consentiti",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => ["Mixed", "Android"],
        "label" => "Blocklist URL",
        "field_id" => "Field id: (blacklistedURLs)",
        "value" => "",
        "input_type" => "todo",
        "description" => "URL specifici che non saranno consentiti",
        "options" => [],
      ],
      [
        "id" => 3,
        "os" => ["Apple"],
        "label" => "Tipo",
        "field_id" => "Field id: (type)",
        "value" => "integrato mostra solo siti web specifici",
        "input_type" => "multiselect",
        "description" => "URL specifici che non saranno consentiti",
        "options" => ["integrato: limita contenuto per adulti", "integrato mostra solo siti web specifici"],
      ],
    ];

    LOG::info($osType);
    if($osType === 'Mixed'){
      foreach ($schema as &$subarray) {
        unset($subarray['os']);
      }
      return $schema;
    }else{
      if (in_array($osType, $this->availableOs)) {
        /*$filteredSchema = array_filter($schema, function ($item) use ($osType) {
         return (in_array(strtolower($osType), $item['os']) || in_array('mixed', $item['os']));
       });
       LOG::info(json_encode($filteredSchema));*/
        // Reindex the array to reset the keys
        return $schema; //array_values($filteredSchema);
      }else{
        return [];
        // throw \Exception::class
      }
    }
  }
}
