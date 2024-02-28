<?php

namespace App\Payloads\Apple;

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

class VpnManagedApplayer extends Payload
{
  public array $availableOs = ['Apple'];

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
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PER_APP_VPN.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "VPN per App",
      "description" => "Usa questa sezione per configurare le connessioni VPN utilizzate dalle App Gestite.",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PER_APP_VPN.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ["Apple"],
        "label" => "Nome",
        "field_id" => "Field id: (UserDefinedName)",
        "value" => "",
        "input_type" => "text",
        "description" => "Nome della configurazione mostrata sul dispositivo",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => ["Apple"],
        "label" => "Tipo connessione",
        "field_id" => "Field id: (VPNType)",
        "value" => "Custom SSL",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["Custom SSL"]
      ],
      [
        "id" => 3,
        "os" => ["Apple"],
        "label" => "Provider VPN",
        "field_id" => "Field id: (VPNSubType)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["---","CISCO LEGACY ANYCONNECT","CISCO ANYCONNECT","JUNIPER SSL","PULSE SECURE","F5 SSL","SONICWALL MOBILE CONNECT",
          "ARUBA VIA", "CHECK POINT MOBILE VPN", "LOOKOUT FOR WORK", "MANUALE"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 5,
        "os" => ["Apple"],
        "label" => "Server remoto",
        "field_id" => "Field id: (CommRemoteAddress)",
        "value" => "App Proxy",
        "input_type" => "text",
        "description" => "Host name o IP address del server",
      ],
      [
        "id" => 6,
        "os" => ["Apple"],
        "label" => "Account",
        "field_id" => "Field id: (AuthName)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 7,
        "os" => ["Apple"],
        "label" => "Dati personalizzati",
        "field_id" => "Field id: (VendorConfig)",
        "value" => "App Proxy",
        "input_type" => "multiselect", // TODo: CHANGE
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
      [
        "id" => 4,
        "os" => ["Apple"],
        "label" => "Tipo Provider",
        "field_id" => "Field id: (ProviderType)",
        "value" => "App Proxy",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ["App Proxy", "Packet Tunnel"]
      ],
    ];

    if (in_array($osType, $this->availableOs)) {
      return $schema;
    }else{
      return [];
      // throw \Exception::class
    }
  }
}
