<?php

// WiFi_Managed.php

namespace App\Payloads\Windows;

use App\Models\Payload;
use App\Models\UemDevice;

// enum OsType: string
// {
//   case IOS = 'iOS';
//   case ANDROID = 'android';
//   case WINDOWS = 'windows';
//   case MACOS = 'macosx';
// }

// enum DeviceModel: string
// {
//   case IPAD = 'ipad';
//   case IPHONE = 'iphone';
//   case IPOD = 'ipod';
//   case APPLETV = 'appletv';
// }

// const LICENSE_ADVANCED_EDUBASE_ACTIVE = "Edu";
// const LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE = true;

class Global_HTTP_Proxy extends Payload
{
  public array $availableOs = ['Windows'];

  const AUTO = 'Auto';
  const MANUAL = 'Manual';

  protected $proxyType;
  protected $proxyServer;
  protected $proxyPort;
  protected $proxyUsername;
  protected $proxyPassword;
  protected $proxyPACURL;
  protected $proxyPACFallbackAllowed;
  protected $proxyCaptiveLoginAllowed;

  /*protected $AndroidCommandType = "CommandType::GLOBAL_PROXY";
  protected $PayloadType = "PayloadType::GLOBAL_HTTP_PROXY";
  protected $ApplePayloadType = "ApplePayloadType::GLOBAL_HTTP_PROXY";*/

  // public function checkCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
  // {

  //   if (!parent::checkWhitelabelIsCompatible())
  //     return false;

  //   $return = false;

  //   if ((defined('LICENSE_ADVANCED_EDUBASE_ACTIVE') && LICENSE_ADVANCED_EDUBASE_ACTIVE === true) || (defined('LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE') && LICENSE_ADVANCED_BUSINESS_PLUS_ACTIVE === true)) {
  //     if (!is_null($device)) {
  //       //niente
  //     } elseif (!is_null($deviceIdentifier)) {
  //       if (is_null($GLOBALS["device"])) {
  //         $GLOBALS["device"] = new UemDevice($deviceIdentifier);
  //       }
  //       $device = &$GLOBALS["device"];
  //     } elseif (!is_null($specialOsType)) {
  //       //niente
  //     } else {
  //       throw new \Exception("all args are null");
  //     }

  //     if (!is_null($device)) $osVersionExp = explode('.', $device->osVersion);

  //     if (($device->osType == OsType::ANDROID && $device->isSupervised === 1) || $specialOsType == OsType::ANDROID) {
  //       $return = true;
  //     } elseif (($device->osType == OsType::IOS && $device->isSupervised === 1) || $specialOsType == OsType::IOS) {
  //       if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD || $device->modelName == DeviceModel::APPLETV) {
  //         $return = true;
  //       } elseif ($specialOsType == OsType::IOS) {
  //         $return = true;
  //       }
  //     } elseif ($device->osType == OsType::WINDOWS || $specialOsType === OsType::WINDOWS) {
  //       $return = false;
  //     }
  //   }

  //   return $return;
  // }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/NETWORKPROXY_CSP.png";
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
    $schema = [
      [
        "id" => 1,
        "os" => ["Windows"],
        "label" => "Impostazioni Proxy per utente",
        "field_id" => "Field id: (ProxySettingsPerUser)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "Aggiunto in Windows 10, versione 1803. Se impostato su 0, abilita la configurazione del proxy come globale, a livello di computer.",
        "options" => []
      ],
      [
        "id" => 2,
        "os" => ["Windows"],
        "label" => "Autodetect",
        "field_id" => "Field id: (AutoDetect)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "Rilevare automaticamente le impostazioni. Se abilitato, il sistema tenta di trovare il percorso di uno script PAC.",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ["Windows"],
        "label" => "Indirizzo dello script PAC che si vuole usare",
        "field_id" => "Field id: (setupScriptUrl)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 4,
        "os" => ["Windows"],
        "label" => "Indirizzo del server proxy",
        "field_id" => "Field id: (ProxyAddress)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 5,
        "os" => ["Windows"],
        "label" => "Eccezioni",
        "field_id" => "Field id: (Exceptions)",
        "value" => "",
        "input_type" => "text",
        "description" => "Gli indirizzi che non devono utilizzare il server proxy. Il sistema non userà il server proxy per gli indirizzi che iniziano con quanto specificato. In caso di più eccezioni, il separatore è il carattere punto e virgola (;).",
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ["Windows"],
        "label" => "Usa Proxy per gli indirizzi locali (Intranet)",
        "field_id" => "Field id: (UseProxyForLocalAddresses)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ]
    ];

    if (in_array($osType, $this->availableOs)) {
      return $schema;
    }else{
      return [];
      // throw \Exception::class
    }
  }
}
