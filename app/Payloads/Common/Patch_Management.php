<?php

namespace App\Payloads\Common;

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

class Patch_Management extends Payload
{
  // OS_SYSTEM_UPDATE_CONFIG
  public array $availableOs = ['Mixed', 'Android', 'Apple', 'Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/OS_SYSTEM_UPDATE_CONFIG.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Patch Management",
      "description" => "Utilizza questa sezione per configurare le impostazioni di sicurezza",
      "img" => "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/PIN_COMPLEXITY.png"
    ];
  }

  public function getSchema(string $osType): array
  {
    $schema = [
      [
        "id" => 1,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" => "Mantenere il sistema operativo aggiornato",
        "field_id" => "Field id: (keepOsUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
      [
        "id" => 2,
        "os" => ['Mixed', 'Android', 'Apple', 'Windows'],
        "label" =>"Mantenere aggiornate le app gestite tramite l\'azione installa applicazione",
        "field_id" => "Field id: (keepAppUpdated)",
        "value" => false,
        "input_type" => "checkbox",
        "description" => "",
        "options" => [],
      ],
    ];

    if($osType === 'Mixed'){
      foreach ($schema as &$subarray) {
        unset($subarray['os']);
      }
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
