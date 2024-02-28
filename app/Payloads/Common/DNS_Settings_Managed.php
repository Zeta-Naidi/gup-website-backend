<?php

namespace App\Payloads\Common;

use App\Exception;
use App\Models\Payload;

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

class DNS_Settings_Managed extends Payload
{

  public array $availableOs = ['Android', 'Apple'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/DNSSETTINGS_MANAGED.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "DNS_Settings_Managed",
      "description" => "Usa questa sezione per impostare il DNS Privato/Sicuro. Queste impostazioni si applicano solo a dispositivi iOS 14+ ed Android 10+. I dispositivi Android devono essere supervisionati.",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/DNSSETTINGS_MANAGED.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ['Android'],
        "label" => "Protocollo DNS",
        "field_id" => "Field id: (DNSProtocol)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Il protocollo di trasporto sicuro usato nella comunicazione con il server DNS.",
        "options" => ["---", "TLS"]
      ],
      [
        "id" => 2,
        "os" => ['Apple'],
        "label" => "Nome profilo",
        "field_id" => "Field id: (PayloadDisplayName)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
      ],
      [
        "id" => 3,
        "os" => ['Apple'],
        "label" => "Abilita Profilo",
        "field_id" => "Field id: (enabled)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
      ],
      [
        "id" => 4,
        "os" => ['Apple'],
        "label" => "Descrizione profilo",
        "field_id" => "Field id: (PayloadDescription)",
        "value" => "",
        "input_type" => "text",
        "description" => "",
      ],
      [
        "id" => 5,
        "os" => ['Apple'],
        "label" => "Rimuovi profilo automaticamente",
        "field_id" => "Field id: (payloadAutomaticRemoval)",
        "value" => "Mai",
        "input_type" => "multiselect",
        "description" => "Impostazioni per la rimozione automatica del profilo. Windows deve essere collegato ad internet per poter aggiornare la schedulazioni.",
        "options" => ["Mai", "In Data", "Dopo intervallo"],
      ],
      [
        "id" => 6,
        "os" => ['Apple'],
        "label" => "Abilita Profilo",
        "field_id" => "Field id: (enabled)",
        "value" => "",
        "input_type" => "todo",
        "description" => "",
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
