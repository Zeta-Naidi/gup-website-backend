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

class Windows_CSP extends Payload
{
  public array $availableOs = ['Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/WINDOWS_CUSTOM_PAYLOAD.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Windows CSP personalizzate",
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
        "label" => "Azioni",
        "field_id" => "Field id: (cspConfiguration)",
        "value" => "",
        "input_type" => "form",
        "description" => "",
        "form_inputs" => [
          [
            "id" => 1,
            "label" => "Endpoint",
            "field_id" => "Field id: (endpoint)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
          ],
          [
            "id" => 2,
            "label" => "Azione Endpoint",
            "field_id" => "Field id: (action)",
            "value" => "---",
            "input_type" => "multiselect",
            "description" => "L\'azione da eseguire",
            "options" => [
              "---",
              "Replace",
              "Get",
              "Delete",
              "Add",
              "CUSTOM_PAYLOAD_TYPE_OPT_EXEC",
            ],
          ],
          [
            "id" => 3,
            "label" => "Data",
            "field_id" => "Field id: (data)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
            "options" => [],
          ],
          [
            "id" => 4,
            "label" => "Meta",
            "field_id" => "Field id: (meta)",
            "value" => "",
            "input_type" => "text",
            "description" => "",
            "options" => [],
          ],
        ],
        "form_outputs" => [],
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
