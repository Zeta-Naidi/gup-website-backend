<?php

namespace App\Entities;

use App\Entities\IEntity;
use Exception;

class PayloadEntity implements IEntity
{
  private string $payloadUUID;
  private int $profileId;
  private string $payloadDisplayName;
  private string $payloadDescription;
  private string $payloadOrganization;
  private string $applePayloadType;
  private array $params;
  private array $config;
  private int $payloadVersion;
  private string $createdAt;

  private array $valuesSet = [];
  private array $creationAttributes = [
    'payloadUUID',
    'profileId',
    'payloadDisplayName',
    'applePayloadType',
    'params',
    'config',
    'payloadVersion',
    'createdAt',
  ];
  private array $permittedUpdateAttributes = [
    'payloadUUID',
    'profileId',
    'payloadDisplayName',
    'payloadDescription',
    'payloadOrganization',
    'applePayloadType',
    'params',
    'config',
    'payloadVersion',
    'createdAt'
  ];

  /**
   * @throws Exception
   */
  public function __construct(array $paramsEntity)
  {
    foreach ($paramsEntity as $key => $value) {
      if ($key === 'createdAt') {
        if(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)){
          // 'createdAt' key exists and the value matches the format 'Y-m-d H:i:s'
          $this->$key = $value;
          $this->valuesSet[] = $key;
        }
        else {
          throw new Exception('DATE_TIME_FORMAT_CREATED_AT_WRONG_PROFILE');
        }
      }

      $this->$key = $value;
      $this->valuesSet[] = $key;
    }
  }
  public function getCreationAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      $entity->$classAttribute = $this->$classAttribute;
    }
    foreach ($this->creationAttributes as $attributeToBePresent) {
      if (!isset($entity->$attributeToBePresent))
        throw new \Exception('MUST_SET_ALL_CREATION_ATTRIBUTES');
    }
    return $entity;
  }
  public function getCreationAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      $entity[$classAttribute] = $this->$classAttribute;
    }
    foreach ($this->creationAttributes as $attributeToBePresent) {
      if (!isset($entity[$attributeToBePresent]))
        throw new \Exception('MUST_SET_ALL_CREATION_ATTRIBUTES_'.$attributeToBePresent);
    }
    return $entity;
  }
  public function getUpdateAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      if (!in_array($classAttribute, $this->permittedUpdateAttributes))
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity->$classAttribute = $this->$classAttribute;
    }

    return $entity;
  }
  public function getUpdateAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      if (!in_array($classAttribute, $this->permittedUpdateAttributes))
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity[$classAttribute] = $this->$classAttribute;
    }
    return $entity;
  }
}
