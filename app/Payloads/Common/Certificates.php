<?php

namespace App\Payloads\Common;

use App\Exception;
use App\Models\Payload;
use App\Models\UemDevice;
use App\Payloads\Android\DeviceModel;
use App\Payloads\Android\OsType;
use Illuminate\Support\Facades\Log;

/*enum PayloadType: string
{
  case RESTRICTIONS = 'RESTRICTIONS';
}

enum ApplePayloadType: string
{
  case RESTRICTIONS = 'RESTRICTIONS';
}

enum DeviceModel: string
{
  case IPAD = 'ipad';
  case IPHONE = 'iphone';
  case IPOD = 'ipod';
  case APPLETV = 'appletv';
}*/

class Certificates extends Payload
{

  public array $availableOs = ['Mixed','Apple', 'Android', 'Windows'];

  /*protected $AndroidCommandType = "CommandType::RESTRICTION";
  protected $PayloadType = PayloadType::RESTRICTIONS;
  protected $ApplePayloadType = ApplePayloadType::RESTRICTIONS;*/

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
      throw new \Exception("all args are null");
    }

    if ($device->osType == OsType::ANDROID || $specialOsType == OsType::ANDROID) {
      $return = true;
    } elseif ($device->osType == OsType::IOS || $specialOsType == OsType::IOS) {
      if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD || $device->modelName == DeviceModel::APPLETV) {
        $return = true;
      } elseif ($specialOsType == OsType::IOS) {
        $return = true;
      }
    } elseif ($device->osType == OsType::WINDOWS || $specialOsType == OsType::WINDOWS) {
      return true;
    }

    return $return;
  }



  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/CERTIFICATES.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Certificati",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/CERTIFICATES.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ['Mixed','Apple', 'Android', 'Windows'],
        "label" => "File",
        "field_id" => "",
        "value" => "",
        "input_type" => "file",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 2,
        "os" => ['Mixed','Apple', 'Android', 'Windows'],
        "label" => "Password (facoltativo)",
        "field_id" => "",
        "value" => "",
        "input_type" => "password",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ['Mixed','Apple', 'Android', 'Windows'],
        "label" => "Carica", // SERVE ??????
        "field_id" => "",
        "value" => "",
        "input_type" => "btn",
        "description" => "",
        "options" => []
      ]
    ];

    if (in_array($osType, $this->availableOs)) {
      foreach ($schema as &$subarray) {
        unset($subarray['os']);
      }
      return $schema;
    }else{
      return [];
      // throw \Exception::class
    }
  }
}
