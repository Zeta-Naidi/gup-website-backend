<?php

namespace App\Services;

class PayloadService
{
  public static function checkChanges(string $payloadexisting, $payloadItem, $payloadName)
  {
    $existingParamsArray = json_decode($payloadexisting, true);
    $newParamsArray = json_decode($payloadItem, true);

    $changedFields = [];

    foreach ($newParamsArray as $newField) {
      $fieldId = $newField['id'];
      $existingField = array_filter($existingParamsArray, function ($field) use ($fieldId) {
        return $field['id'] === $fieldId;
      });

      if (empty($existingField) || $newField['value'] !== reset($existingField)['value']) {
        $changedFields[] = [
          'id' => $fieldId,
          'old_value' => !empty($existingField) ? reset($existingField)['value'] : null,
          'new_value' => $newField['value'],
        ];
      }
    }
    return [
      'value' => $changedFields,
      'action' => 'update',
      'payloadName' => $payloadName,
    ];
  }
}
