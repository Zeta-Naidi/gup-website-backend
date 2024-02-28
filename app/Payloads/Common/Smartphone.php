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

class Smartphone extends Payload
{
  public array $availableOs = ['Android', 'Windows'];

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
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/CELLULAR.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Cellulare",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    // todo: add         "os" => ["Windows", "Android"],
    $schema = [
      [
        "id" => 1,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Nome connessione APN",
        "field_id" => "Field id: (attachAPNConnectionName)",
        "value" => "",
        "input_type" => "text",
        "description" => ""
      ],
      [
        "id" => 2,
        "os" => ["Windows"],
        "category" => "APN",
        "label" => "APN",
        "field_id" => "Field id: (attachAPNName_Windows)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Tipologia IP APN",
        "field_id" => "Field id: (attachAPNIpType)",
        "value" => "IPV4",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "IPV4",
          "IPV6",
          "Tutti",
          "IPV6 con IPV4 fornito da 46xlat"
        ]
      ],
      [
        "id" => 4,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Richiedi APN come parte di collegamento LTE",
        "field_id" => "Field id: (APNIsAttachAPN)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => ""
      ],
      [
        "id" => 5,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "ATTACHAPNCLASSID",
        "field_id" => "Field id: (attachAPNClassId)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "dependable" => [
          "id" => 4,
          "value" => true
        ],
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Tipo di autenticazione",
        "field_id" => "Field id: (attachAPNAuthType_Windows)",
        "value" => "None",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "None",
          "Automatico",
          "PAP",
          "CHAP",
          "MSCHAPV2"
        ]
      ],
      [
        "id" => 7,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Username",
        "field_id" => "Field id: (attachAPNUsername_Windows)",
        "value" => "",
        "input_type" => "text",
        "description" => ""
      ],
      [
        "id" => 8,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Password",
        "field_id" => "Field id: (attachAPNPassword_Windows)",
        "value" => "",
        "input_type" => "text",
        "description" => ""
      ],
      [
        "id" => 9,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Connetti all'APN ogni volta che è disponibile una connessione",
        "field_id" => "Field id: (APNAlwaysOn)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => ""
      ],
      [
        "id" => 10,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Abilita connessione",
        "field_id" => "Field id: (APNEnabled)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => ""
      ],
      [
        "id" => 11,
        "os" => ["Windows"],
        "category" => "Impostazioni APN di default",
        "label" => "Abilita connessione",
        "field_id" => "Field id: (APNEnabled)",
        "value" => "Consentito",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "Non consentito",
          "Consentito",
          "se roaming nazionale",
          "solo se roaming nazionale",
          "solo se roaming internazionale",
          "solo se roaming attivo"
        ]
      ],
      [
        "id" => 12,
        "os" => ["Windows"],
        "category" => "Impostazioni",
        "label" => "Consenti connessioni ad APN diversi da quello aziendale",
        "field_id" => "Field id: (APNallowUserControl)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => ""
      ],
      [
        "id" => 13,
        "os" => ["Windows"],
        "category" => "Impostazioni",
        "label" => "Mostra altri APN oltre a quello aziendale",
        "field_id" => "Field id: (APNhideView)",
        "value" => false,
        "input_type" => "checkbox",
        "dependable" => [
          "id" => 12,
          "value" => true
        ],
        "description" => ""
      ],
      [
        "id" => 13,
        "os" => ["Android"],
        "label" => "Tipologia configurazione APN",
        "field_id" => "Field id: (apnConfigurationType)",
        "value" => "APN di default",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "APN di default",
          "APN dati",
          "APN di default e dati",
        ]
      ],
      [
        "id" => 14,
        "os" => ["Android"],
        "category" => "APN",
        "label" => "APN",
        "field_id" => "Field id: (attachAPNName)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 15,
        "os" => ["Android"],
        "category" => "Impostazioni APN di default",
        "label" => "Tipo di autenticazione",
        "field_id" => "Field id: (attachAPNAuthType)",
        "value" => "None",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "None",
          "Automatico",
          "PAP",
          "CHAP",
          "MSCHAPV2"
        ]
      ],
      [
        "id" => 16,
        "os" => ["Android"],
        "category" => "Impostazioni APN di default",
        "label" => "Username",
        "field_id" => "Field id: (attachAPNUsername)",
        "value" => "",
        "input_type" => "text",
        "description" => ""
      ],
      [
        "id" => 17,
        "os" => ["Android"],
        "category" => "Impostazioni APN di default",
        "label" => "Password",
        "field_id" => "Field id: (attachAPNPassword)",
        "value" => "",
        "input_type" => "text",
        "description" => ""
      ],
      [
        "id" => 18,
        "os" => ["Android"],
        "category" => "Impostazioni APN di default",
        "label" => "Tipologia MVNO",
        "field_id" => "Field id: (MVNOType)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "---",
          "SPN",
          "IMSI",
          "ICCID"
        ]
      ],
      [
        "id" => 19,
        "os" => ["Android"],
        "category" => "Impostazioni APN dati",
        "label" => "????",
        "field_id" => "Field id: ()",
        "value" => "",
        "input_type" => "form",
        "description" => "",
        "form_inputs" => [
          [
            "id" => 1,
            "label" => "APN",
            "field_id" => "Field id: (Name)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
          ],
          [
            "id" => 2,
            "label" => "Tipo di autenticazione",
            "field_id" => "Field id: (AuthenticationType)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "",
            "options" => ["---", "PAP", "CHAP", "PAP_OR_CHAP"],
          ],
          [
            "id" => 3,
            "label" => "Bitmask",
            "field_id" => "Field id: (Bitmask)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "",
            "options" => ["Predefinito","MMS","SUPL","DUN","HIPRI","FOTA","IMS","CBS","IA","EMERGENCY"],
          ],
          [
            "id" => 4,
            "label" => "Tipologia MVNO",
            "field_id" => "Field id: (MVNOType)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "",
            "options" => [ "---", "SPN","IMSI","ICCID"]
          ],
          [
            "id" => 5,
            "label" => "Username",
            "field_id" => "Field id: (Username)",
            "value" => "",
            "input_type" => "text",
            "description" => "Nome utente per la connessione a una rete wireless. Parametri wildcard disponibili (%email%, %username%, %fullname%, %firstname%, %surname%)",
          ],
          [
            "id" => 6,
            "label" => "Password",
            "field_id" => "Field id: (Password)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
          ],
          [
            "id" => 7,
            "label" => "Proxy Server",
            "field_id" => "Field id: (ProxyServer)",
            "value" => "",
            "input_type" => "text",
            "description" => "Nome host o indirizzo IP e numero di porta per il server proxy",
          ],
          [
            "id" => 8,
            "label" => "Porta",
            "field_id" => "Field id: (ProxyServerPort)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
          ],
          [
            "id" => 9,
            "label" => "APN",
            "field_id" => "Field id: (AllowedProtocolMask)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
          ],
          [
            "id" => 10,
            "label" => "Versione IP supportata",
            "field_id" => "Field id: (AllowedProtocolMask)",
            "value" => "",
            "input_type" => "multiselect",
            "description" => "",
            "options" => ["---", "IPv4", "IPv6", "Tutti"],
          ],
          [
            "id" => 10,
            "label" => "Versione IP supportata in roaming",
            "field_id" => "Field id: (AllowedProtocolMaskInRoaming)",
            "value" => "",
            "input_type" => "multiselect",
            "description" => "",
            "options" => ["---", "IPv4", "IPv6", "Tutti"],
          ],
        ],
        "form_outputs" => []
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
