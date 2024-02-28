<?php

namespace App\Payloads\Windows;

use App\Exception;
use App\Models\Payload;

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

class Defender extends Payload
{

  public array $availableOs = ['Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/DEFENDER.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Defender",
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
        "label" => "Abilita scansione automatica Anti-malware",
        "field_id" => "Field id: (enableMalwareScan)",
        "value" => true,
        "input_type" => "checkbox",
        "description" =>
        "<b>Disponibilità:</b> Disponibile con Android e Windows.",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => ["Windows"],
        "label" => "Abilita scansione completa del Sistema Operativo",
        "field_id" => "Field id: (enableCompleteOSScan)",
        "value" => false,
        "input_type" => "checkbox",
        "description" =>
        "<b>Disponibilità:</b> Disponibile con Android e Windows.",
        "options" => [],
      ],
      [
        "id" => 3,
        "os" => ["Windows"],
        "label" => "Descrizione Profilo",
        "field_id" => "Field id: (enabled)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 4,
        "os" => ["Windows"],
        "label" => "Rimuovi Profilo automaticamente",
        "field_id" => "Field id: (payloadAutomaticRemoval)",
        "value" => "Mai",
        "input_type" => "multiselect",
        "description" =>
        "Impostazioni per la rimozione automatica del profilo. Windows deve essere collegato ad internet per poter aggiornare la schedulazioni.",
        "options" => ["Mai", "Indata", "Dopo Intervallo"],
      ],
      [
        "id" => 5,
        "os" => ["Windows"],
        "label" => "Limita a periodo temporale definito",
        "field_id" => "Field id: (limitOnDates)",
        "value" => "",
        "input_type" => "todo",
        "description" =>
        "Limita l\'attivazione del profilo nei periodi temporali specificati. <br> Compatibile con iOS, tvOS, iPadOS, Android e Windows. iOS, tvOS, iPadOS e Windows devono essere collegati ad internet per poter aggiornare la schedulazioni.",
        "options" => [],
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
