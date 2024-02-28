<?php

namespace App\Security;


use Illuminate\Support\Facades\Log;

class MispFormatter
{


  public static function formatAttributesAndDeviceObject($event)
  {
    $attribute = self::calcAttribute($event);
    $deviceObject = self::calcDeviceObject($event);

    return [
      "attribute" => $attribute['success'] ? $attribute['payload'] : [],
      "device" => $deviceObject['success'] ? $deviceObject['payload'] : null
    ];

  }

  private static function calcDeviceObject($event)
  {
    try {
      $deviceObject = new \stdClass();
      $deviceObject->name = 'device';
      $deviceObject->event_id = (string)$event->id;
      $deviceObject->distribution = 0;
      $attributes = [];
      $newAttribute = new \stdClass();
      $newAttribute->distribution = 0;
      $newAttribute->object_relation = 'name';
      $newAttribute->category = "Other";
      $newAttribute->type = "text";
      $newAttribute->value = $event->deviceSerialNumber;
      $attributes[] = $newAttribute;

      if (isset($event->name)) {
        $newAttribute = new \stdClass();
        $newAttribute->object_relation = 'alias';
        $newAttribute->value = $event->name;
        $attributes[] = $newAttribute;
      }

      $newAttribute = new \stdClass();
      $newAttribute->object_relation = 'device-type';
      $newAttribute->value = ($event->osType == 'ios' || $event->osType == 'android') ? 'Mobile' : 'PC';
      $attributes[] = $newAttribute;

      $newAttribute = new \stdClass();
      $newAttribute->object_relation = 'OS';
      $newAttribute->value = $event->osType;
      $attributes[] = $newAttribute;

      $newAttribute = new \stdClass();
      $newAttribute->object_relation = 'version';
      $newAttribute->value = $event->osVersion;
      $attributes[] = $newAttribute;
      $deviceObject->Attribute = $attributes;
      return ['success' => true, 'payload' => [$deviceObject]];
    } catch (\Exception $e) {
      return ['success' => false, 'payload' => null];
    }
  }


  private static function calcAttribute($event)
  {
    try {
      $attributeCreated = new \stdClass();
      $attributeCreated->to_ids = false;
      $attributeCreated->distribution = "0";
      $attributeCreated->disable_correlation = true;
      $attributeCreated->timestamp = strtotime($event->detectionDate);

      if ($event->type == 1) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'External analysis';
          $attributeCreated->type = 'vulnerability';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 7) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Payload delivery';
          $attributeCreated->type = 'malware-type';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 9) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Network activity';
          $attributeCreated->type = 'domain';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 10) {
        if (!empty($event->subject) && !empty($event->subject->target)) {
          $ip_pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[0-9a-fA-F:]+)$/i';
          if (preg_match($ip_pattern, $event->subject->target)) {
            $attributeCreated->category = 'Network activity';
            $attributeCreated->type = 'ip-dst';
            $attributeCreated->value = $event->subject->target;
          } else {
            $attributeCreated->category = 'Network activity';
            $attributeCreated->type = 'domain';
            $attributeCreated->value = $event->subject->target;
          }
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 11) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Network activity';
          $attributeCreated->type = 'mac-address';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 18 || $event->type == 19 || $event->type == 20) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Payload delivery';
          $attributeCreated->type = 'mobile-application-id';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 23 || $event->type == 25 || $event->type == 33) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Network activity';
          $attributeCreated->type = 'domain';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else if ($event->type == 24) {
        if (!empty($event->subject)) {
          $attributeCreated->category = 'Network activity';
          $attributeCreated->type = 'ip-dst';
          $attributeCreated->value = $event->subject;
        } else return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      } else {
        return ['success' => false, 'payload' => 'ATTRIBUTE_NOT_FOUND'];
      }

      return ['success' => true, 'payload' => $attributeCreated];
    } catch (\Exception $e) {
      return ['success' => false, 'payload' => null];
    }
  }


}
