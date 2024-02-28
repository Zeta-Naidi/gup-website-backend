<?php

namespace App\Payloads\Common;namespace App\Payloads\Android;
use App\Models\Payload;
use App\Models\UemDevice;
use App\Exception;

/*enum PayloadType: string
{
  case EMAIL = 'EMAIL';
}

enum ApplePayloadType: string
{
  case EMAIL = 'EMAIL';
}

enum DeviceModel: string
{
  case IPAD = 'ipad';
  case IPHONE = 'iphone';
  case IPOD = 'ipod';
  case APPLETV = 'appletv';
}*/


class Email_Managed extends Payload
{
  public array $availableOs = ['Mixed','Apple','Windows'];

  const ADULT_FILTER = 'adultFilter';
  const SITES_WEB_FILTER = 'websiteOnly';
  const THIRD_PARTY_APP_FILTER = 'thirdPartyApp';

  protected $emailAccountDescription;
  protected $emailAccountType;
  protected $incomingMailServerIMAPPathPrefix;
  protected $incomingMailServerAuthentication;
  protected $incomingMailServerHostName;
  protected $incomingMailServerPortNumber;
  protected $incomingPassword;
  protected $outgoingMailServerAuthentication;
  protected $outgoingMailServerHostName;
  protected $outgoingMailServerPortNumber;
  protected $outgoingPassword;
  protected $incomingMailServerUseSSL;
  protected $outgoingMailServerUseSSL;
  protected $outgoingPasswordSameAsIncomingPassword;
  protected $preventMove;
  protected $preventAppSheet;
  protected $SMIMEEnablePerMessageSwitch;
  protected $disableMailRecentsSyncing;
  protected $allowMailDrop;
  protected $emailAccountName;
  protected $emailAddress;
  protected $incomingMailServerUsername;
  protected $outgoingMailServerUsername;
  protected $SMIMESigningCertificateUUID;
  protected $SMIMEEncryptionCertificateUUID;
  protected $SMIMESigningEnabled;
  protected $SMIMEEncryptionEnabled;
  protected $VPNUUID;
  protected $emailGuid;
  protected $action;

  /*protected $AndroidCommandType = NULL;
  protected $PayloadType = PayloadType::EMAIL;
  protected $ApplePayloadType = ApplePayloadType::EMAIL;*/



  public function checkCompatibility($deviceIdentifier = NULL, UemDevice $device = NULL, string $specialOsType = NULL): bool
  {

    if (!parent::checkWhitelabelIsCompatible())
      return false;

    $return = false;

    if ((defined('LICENSE_ADVANCED_STANDARD_ACTIVE') && LICENSE_ADVANCED_STANDARD_ACTIVE === true)) {
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
        $return = false;
      } elseif ($device->osType == OsType::IOS || $specialOsType == OsType::IOS) {
        if ($device->modelName == DeviceModel::IPAD || $device->modelName == DeviceModel::IPHONE || $device->modelName == DeviceModel::IPOD) {
          if (!$device->isSharedIpad) $return = true;
        } elseif ($specialOsType == OsType::IOS) {
          $return = true;
        }
      } elseif ($device->osType == OsType::WINDOWS || $specialOsType == OsType::WINDOWS) {
        $return = true;
      }
    }

    return $return;
  }

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/EMAIL.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Mail",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    // TODO: rivedere         "os" => [], con array di OS
    $schema = [
      [
        "id" => 1,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Generale",
        "label" => "Tipo di Account",
        "field_id" => "Field id: (emailAccountType)",
        "value" => "IMAP",
        "input_type" => "multiselect",
        "description" => "Il protocollo per l'accesso all'account email",
        "options" => [
          "IMAP",
          "POP"
        ]
      ],
      [
        "id" => 2,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Generale",
        "label" => "Nome visualizzato per l'utente",
        "field_id" => "Field id: (emailAccountName)",
        "value" => "",
        "input_type" => "text",
        "description" => "Il nome visualizzato per l'utente. Parametri wildcard disponibili (%email%, %username%, %fullname%, %firstname%, %surname%)",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Generale",
        "label" => "Indirizzo e-mail",
        "field_id" => "Field id: (emailAddress)",
        "value" => "",
        "input_type" => "text",
        "description" => "L'indirizzo dell'account. Parametri wildcard disponibili (%email%, %username%, %fullname%, %firstname%, %surname%)",
        "options" => []
      ],
      [
        "id" => 4,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Entrata",
        "label" => "Server di posta e porta",
        "field_id" => "Field id: (incomingMailServerHostName)",
        "value" => "",
        "input_type" => "text",
        "description" => "Nome host o indirizzo IP e numero porta per la posta in entrata",
        "options" => []
      ],
      [
        "id" => 5,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Entrata",
        "label" => "Porta",
        "field_id" => "Field id: (incomingMailServerPortNumber)",
        "value" => "143",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Entrata",
        "label" => "Nome Utente",
        "field_id" => "Field id: (incomingMailServerUsername)",
        "value" => "",
        "input_type" => "text",
        "description" => "Nome utente utilizzato per connettersi al server di posta in entrata. Parametri wildcard disponibili (%email%, %username%, %fullname%, %firstname%, %surname%)",
        "options" => []
      ],
      [
        "id" => 7,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Entrata",
        "label" => "Tipo di autenticazione",
        "field_id" => "Field id: (incomingMailServerAuthentication)",
        "value" => "Nessuno",
        "input_type" => "multiselect",
        "description" => "Metodo di autenticazione per il server di posta in entrata",
        "options" => [
          "Nessuno",
          "Password",
          "MD5 challenge Response",
          "Ntlm",
          "Digest MD5 Http"
        ]
      ],
      [
        "id" => 8,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Entrata",
        "label" => "Utilizza SSL",
        "field_id" => "Field id: (incomingMailServerUseSSl)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Recupera posta in entrata mediante SSL",
        "options" => []
      ],
      [
        "id" => 9,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Uscita",
        "label" => "Server di posta e porta",
        "field_id" => "Field id: (outgoingMailServerHostName)",
        "value" => "",
        "input_type" => "text",
        "description" => "Nome host o indirizzo IP e numero porta per la posta in uscita",
        "options" => []
      ],
      [
        "id" => 10,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Uscita",
        "label" => "Porta",
        "field_id" => "Field id: (outgoingMailServerPortNumber)",
        "value" => "587",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 11,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Uscita",
        "label" => "Utilizza SSL",
        "field_id" => "Field id: (outgoingMailServerUseSSL)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Recupera posta in entrata mediante SSL",
        "options" => []
      ],
      [
        "id" => 12,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Uscita",
        "label" => "Il nome di un servizio esente da Always On VPN. CellularServices è disponibile in iOS 11.3 e versioni successive; esenta VoLTE, IMS e MMS. WiFiCalling è esentato in iOS 13.4 e versioni successive.",
        "field_id" => "Field id: (outgoingMailServerHostName)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Recupera posta in entrata mediante SSL",
        "options" => [
          "---",
          "GOOGLE",
          "YAHOO",
          "OUTLOOK",
          "OTHER"
        ]
      ],
      [
        "id" => 13,
        "os" => ["Mixed", "Apple", "Windows"],
        "category" => "Posta in Uscita",
        "label" => "Guid email",
        "field_id" => "Field id: (emailGuid)",
        "value" => "{e2a4ae55-4c53-1c86-9869-7080d4653f84}",
        "input_type" => "text",
        "description" => "",
        "options" => []
      ]
    ];

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
