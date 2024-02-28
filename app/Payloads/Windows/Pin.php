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

class Pin extends Payload
{
  public array $availableOs = ['Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Pin",
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
        "label" => "Cifre",
        "field_id" => "Field id: (digits)",
        "value" => "Consentito",
        "input_type" => "multiselect",
        "description" => "Indica se è obbligatorio, consentito o vietato l'utilizzo delle cifre",
        "options" => [
          "Consentito",
          "Obbligatorio",
          "Non consentito"
        ]
      ],
      [
        "id" => 2,
        "os" => ["Windows"],
        "label" => "Lettere minuscole",
        "field_id" => "Field id: (lowercaseLetters)",
        "value" => "Consentito",
        "input_type" => "multiselect",
        "description" => "Indica se è obbligatorio, consentito o vietato l'utilizzo delle lettere minuscole",
        "options" => [
          "Consentito",
          "Obbligatorio",
          "Non consentito"
        ]
      ],
      [
        "id" => 3,
        "os" => ["Windows"],
        "label" => "Lettere maiuscole",
        "field_id" => "Field id: (uppercaseLetters)",
        "value" => "Consentito",
        "input_type" => "multiselect",
        "description" => "Indica se è obbligatorio, consentito o vietato l'utilizzo delle lettere maiuscole",
        "options" => [
          "Consentito",
          "Obbligatorio",
          "Non consentito"
        ]
      ],
      [
        "id" => 4,
        "os" => ["Windows"],
        "label" => "Caratteri speciali",
        "field_id" => "Field id: (specialCharacters)",
        "value" => "Consentito",
        "input_type" => "multiselect",
        "description" => "Indica se è obbligatorio, consentito o vietato l'utilizzo di caratteri speciali",
        "options" => [
          "Consentito",
          "Obbligatorio",
          "Non consentito"
        ]
      ],
      [
        "id" => 5,
        "os" => ["Windows"],
        "label" => "Periodo massimo di validità del pin (1-730 giorni o nessuno)",
        "field_id" => "Field id: (expiration)",
        "value" => "",
        "input_type" => "number",
        "description" => "Giorni dopo cui il pin deve essere cambiato",
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ["Windows"],
        "label" => "Cronologia pin di accesso (1-50 codici di accesso o nessuno)",
        "field_id" => "Field id: (history)",
        "value" => "",
        "input_type" => "number",
        "description" => "Numero di pin unici del riutilizzo",
        "options" => []
      ],
      [
        "id" => 7,
        "os" => ["Windows"],
        "label" => "Lunghezza minima del PIN",
        "field_id" => "Field id: (minPINLength)",
        "value" => "",
        "input_type" => "number",
        "description" => "4-127",
        "options" => []
      ],
      [
        "id" => 8,
        "os" => ["Windows"],
        "label" => "Numero massimo di caratteri del PIN",
        "field_id" => "Field id: (maxPINLength)",
        "value" => "",
        "input_type" => "number",
        "description" => "4-127",
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
