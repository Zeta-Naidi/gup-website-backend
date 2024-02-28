<?php

namespace App\Payloads\Common;

use App\Exception;
use App\Models\Payload;
use App\Models\UemDevice;
use App\Payloads\Android\DeviceModel;
use App\Payloads\Android\OsType;

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

class Background extends Payload
{
  public array $availableOs = ['Mixed','Android','Windows'];


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
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WALLPAPER.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Sfondo",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType) :array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ["Mixed", "Windows", "Android"],
        "label" => "URL immagine",
        "field_id" => "Field id: (imageUrl)",
        "value" => "",
        "input_type" => "text",
        "description" => "L'URL che punta ad una immagine JPEG o PNG",
        "options" => []
      ],
      [
        "id" => 2,
        "os" => ["Mixed", "Windows", "Android"],
        "label" => "Schermata",
        "field_id" => "Field id: (where)",
        "value" => "Blocco e Home",
        "input_type" => "multiselect",
        "description" => "Regola di applicazione dello sfondo.",
        "options" => [
          "Blocco e Home",
          "Bloco",
          "Home"
        ]
      ],
      [
        "id" => 3,
        "os" => ["Android"],
        "label" => "Non applicare su dispositivi VR",
        "field_id" => "Field id: (ignoreVr)",
        "value" => "Blocco e Home",
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
    ];

    if($osType === 'Mixed'){
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
