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

class Geofence extends Payload
{
  public array $availableOs = ['Mixed', 'Android', 'Apple', 'Windows'];

  public function getIcon(): string
  {
    return "https://nextcloud.chimpa.eu/testmdm/panel/latest/assets/img/payloads/GEOFENCE_LOGS.png";
  }

  public function getConfig(): array
  {
    return [
      "show" => true,
      "title" => "Geofence",
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
        "label" => "Aree geofence",
        "field_id" => "Field id: (geofenceAreas)",
        "value" => "",
        "input_type" => "todo",
        "description" => "Selsziona le aree Geofence definite in Monitoraggio > Geofence",
        "options" => []
      ]
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
