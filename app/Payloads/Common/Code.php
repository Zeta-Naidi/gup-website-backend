<?php

namespace App\Payloads\Common;

use App\Exception;
use App\Models\Payload;
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

class Code extends Payload
{
  public array $availableOs = ['Mixed', 'Android', 'Apple', 'Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PASSCODE.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Codice",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PASSCODE.png"
    ];
  }

  public function getSchema($osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ['Mixed'],
        "label" => "Lunghezza minima",
        "field_id" => "Field id: (minLength)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 2,
        "os" => ['Mixed'],
        "label" => "Periodo massimo di validità del codice di accesso (2-730 giorni o nessuno)",
        "field_id" => "Field id: (maxPINAgeInDays)",
        "value" => false,
        "input_type" => "number",
        "rules" => [
          [
            "min" => 2,
            "max" => 730
          ]
        ],
        "description" => "Giorni dopo cui il codice deve essere cambiato",
        "options" => []
      ],
      [
        "id" => 3,
        "os" => ['Mixed'],
        "label" => "Cronologia codici di accesso (1-50 codici di accesso o nessuno)",
        "field_id" => "Field id: (pinHistory)",
        "value" => true,
        "input_type" => "number",
        "description" => "Numero di codici di accesso unici prima del riutilizzo",
        "options" => []
      ],
      [
        "id" => 4,
        "os" => ['Mixed'],
        "label" => "Numero massimo di tentativi non riusciti",
        "field_id" => "Field id: (maxFailedAttempts)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "---",
          1,
          2,
          3,
          4,
          5,
          6
        ]
      ],
      [
        "id" => 5,
        "os" => ['Mixed'],
        "label" => "Consenti Fotocamera in Blocco Schermo",
        "field_id" => "Field id: (allowUnsecureCamera)",
        "value" => "",
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 6,
        "os" => ['Mixed'],
        "label" => "Massimo timeout per il Blocco Schermo automatico",
        "field_id" => "Field id: (lockscreenTimeout)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => [
          "---",
          "1 secondo",
          "10 secondi",
          "1 minuto",
          "2 minuti",
          "2 minuti",
          "5 minuti",
          "10 minuti",
          "15 minuti",
          "30 minuti"
        ]
      ],
      [
        "id" => 7,
        "os" => ['Apple'],
        "label" => "Consenti valore semplice",
        "field_id" => "Field id: (allowSimple)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 8,
        "os" => ['Apple'],
        "label" => "Richiedi Passcode sul Dispositivo",
        "field_id" => "Field id: (forcePIN)",
        "value" => false,
        "input_type" => "number",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 9,
        "os" => ['Apple'],
        "label" => "Richiede valore alfanumerico",
        "field_id" => "Field id: (requireAlphanumeric)",
        "value" => false,
        "input_type" => "number",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 10,
        "os" => ['Apple'],
        "label" => "Numero minimo di caratteri complessi",
        "field_id" => "Field id: (minComplexChars)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ['---', 1,2,3,4]
      ],
      [
        "id" => 11,
        "os" => ['Apple'],
        "label" => "Intervallo massimo senza richiedere codice",
        "field_id" => "Field id: (maxGracePeriod)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "",
        "options" => ['---', "Immediatamente","1 Minuto","5 Minuti"," 15 Minuti", "1 Ora", "4 Ore"]
      ],
      [
        "id" => 12,
        "os" => ['Android'],
        "label" => "Policy Codice di sblocco Dispositivo",
        "field_id" => "Field id: (devicePasswordQualityNew)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "Disponibilità: Disponibile solo con Android.",
        "options" => ['---', 'Bassa', 'Media', 'Alta']
      ],
      [
        "id" => 13,
        "os" => ['Android'],
        "label" => "Policy Codice di sblocco Profilo (solo per Profilo di lavoro)",
        "field_id" => "Field id: (profilePasswordQualityNew)",
        "value" => "---",
        "input_type" => "multiselect",
        "description" => "<b>Disponibilità:</b> Disponibile solo con Android Nougat 7.0 o versioni successive.",
        "options" => ['---', 'Bassa', 'Media', 'Alta']
      ],
      [
        "id" => 14,
        "os" => ['Android'],
        "label" => "Specifica Policy Password LEGACY per vecchie versioni di Android",
        "field_id" => "Field id: (overrideLegacyPolicies)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 15,
        "os" => ['Android'],
        "label" => "Consenti Passcode Unico (solo per Profilo di lavoro)",
        "field_id" => "Field id: (allowUnifiedPassword)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 16,
        "os" => ['Android'],
        "label" => "Consenti sblocco con scansione Biometrica",
        "field_id" => "Field id: (allowBiometrics)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 17,
        "os" => ['Android'],
        "label" => "Consenti Google Smart Lock e altri trust agents (Android Lollipop 5.x necessita Supervisione)",
        "field_id" => "Field id: (allowTrustAgents)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "",
        "options" => []
      ],
      [
        "id" => 18,
        "os" => ['Android'],
        "label" => "Consenti Notifiche in Blocco Schermo",
        "field_id" => "Field id: (allowAllNotifications)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattiva, le notifiche non verranno mostrate nella schermata di blocco schermo.<b>Disponibilità:</b> Disponibile solo in Android.",
        "options" => []
      ],
      [
        "id" => 19,
        "os" => ['Android'],
        "label" => "Consenti Anteprima Notifiche in Blocco Schermo",
        "field_id" => "Field id: (allowUnredactedNotifications)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Quando questa opzione è disattiva, le anteprime delle notifiche non verranno mostrate nella schermata di blocco schermo.<b>Disponibilità:</b> Disponibile solo in Android.",
        "options" => []
      ],
      [
        "id" => 20,
        "os" => ['Windows'],
        "label" => "Richiedi password complessa",
        "field_id" => "Field id: (allowComplexPassword)",
        "value" => true,
        "input_type" => "checkbox",
        "description" => "Se abilitato, la password deve avere almeno sei caratteri, non deve contenere il nome dell'utente e deve rispettare almeno tre dei seguenti requisiti: almeno una lettera minuscola, almeno una maiuscola, almeno una cifra, almeno un carattere speciale",
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
