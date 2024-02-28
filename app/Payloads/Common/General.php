<?php

// WiFi_Managed.php

namespace App\Payloads\Common;

use App\Models\Payload;
use App\Models\UemDevice;
use App\Payloads\Android\DeviceModel;
use App\Payloads\Android\OsType;
use Illuminate\Support\Facades\Log;
use const App\Payloads\Android\LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE;
use const App\Payloads\Android\LICENSE_ADVANCED_EDUBASE_ACTIVE;

/*enum OsType: string
{
  case IOS = 'iOS';
  case ANDROID = 'android';
  case WINDOWS = 'windows';
  case MACOS = 'macosx';
}*/

class General extends Payload
{
  public array $availableOs = ['Mixed', 'Android', 'Apple', 'Windows'];

  const AUTO = 'Auto';
  const MANUAL = 'Manual';



  /*protected $AndroidCommandType = "CommandType::GLOBAL_PROXY";
  protected $PayloadType = "PayloadType::GLOBAL_HTTP_PROXY";
  protected $ApplePayloadType = "ApplePayloadType::GLOBAL_HTTP_PROXY";*/
  public function checkCompatibility($osType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ($osType == "android" || $osType == "ios" || $osType == "windows" || $osType == "mixed") {
      $return = true;
    }

    return $return;
  }
  public function checkDeviceCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ((defined('LICENSE_ADVANCED_EDUBASE_ACTIVE') && LICENSE_ADVANCED_EDUBASE_ACTIVE === true) || (defined('LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE') && LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE === true)) {
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

      if (!is_null($device)) $osVersionExp = explode('.', $device->osVersion);

      if (($device->osType == OsType::ANDROID && $device->isSupervised === 1) || $specialOsType == OsType::ANDROID) {
        $return = true;
      } elseif (($device->osType == OsType::IOS && $device->isSupervised === 1) || $specialOsType == OsType::IOS) {
        if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD || $device->modelName == DeviceModel::APPLETV) {
          $return = true;
        } elseif ($specialOsType == OsType::IOS) {
          $return = true;
        }
      } elseif ($device->osType == OsType::WINDOWS || $specialOsType === OsType::WINDOWS) {
        $return = false;
      }
    }

    return $return;
  }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/cloud/panel/v35/assets/img/payloads/CONFIGURATION.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => false,
    ];
  }

  public function getSchema($osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Nome Profilo",
        "field_id" => "Field id: (PayloadDisplayName)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 2,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Abilita Profilo",
        "field_id" => "Field id: (enabled)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Descrizione Profilo",
        "field_id" => "Field id: (PayloadDescription)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 4,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Rimuovi Profilo automaticamente",
        "field_id" => "Field id: (payloadAutomaticRemoval)",
        "value" => "Mai",
        "input_type" => "multiselect",
        "description" => "Impostazioni per la rimozione automatica del profilo. Windows deve essere collegato ad internet per poter aggiornare la schedulazioni.",
        "options" => [
          "Mai",
          "Indata",
          "Dopo Intervallo"
        ]
      ],
      [
        "id" => 5,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Limita a WiFi raggiungibili definite",
        "field_id" => "Field id: (limitOnDates)",
        "value" => "Mai",
        "input_type" => "todo",
        "description" => "Limita l'attivazione del profilo nei periodi temporali specificati.<br>Compatibile con iOS, tvOS, iPadOS, Android e Windows. iOS, tvOS, iPadOS e Windows devono essere collegati ad internet per poter aggiornare la schedulazioni.",
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ['Android'],
        "label" => "Limita a WiFi raggiungibili definite",
        "field_id" => "Field id: (limitOnWifiRange)",
        "value" => "Mai",
        "input_type" => "todo",
        "description" => "Limita l'attivazione del profilo nel raggio delle reti WiFi specificate.<br>I servizi di localizzazione devono essere attivi da Android 10 al 12.",
        "options" => []
      ],
      [
        "id" => 7,
        "os" => ['Android'],
        "label" => "Limita ad IP Pubblici definiti",
        "field_id" => "Field id: (limitOnPublicIps)",
        "value" => "Mai",
        "input_type" => "todo",
        "description" => "Limita l'attivazione del profilo sui dispositivi che fanno richieste da uno degli IP pubblici specificati.",
        "options" => []
      ]
    ];

    if ($osType === 'Mixed') {
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
