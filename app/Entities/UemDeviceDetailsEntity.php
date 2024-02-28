<?php

namespace App\Entities;
class UemDeviceDetailsEntity implements IEntity
{
  private int $deviceId;
  private int $parentDeviceId;
  private array $hardwareDetails;
  private array $technicalDetails;
  private array $restrictions;
  private array $locationDetails;
  private array $networkDetails;
  private array $accountDetails;
  private array $osDetails;
  private array $securityDetails;
  private array $androidConfigs;
  private array $appleConfigs;
  private array $installedApps;
  private array $miscellaneous;

  private array $valuesSet = [];

  private array $creationAttributes = [
    'deviceId',
    'parentDeviceId',
    'hardwareDetails',
    'technicalDetails',
    'restrictions',
    'locationDetails',
    'networkDetails',
    'accountDetails',
    'osDetails',
    'securityDetails',
    'androidConfigs',
    'appleConfigs',
    'installedApps',
    'miscellaneous',
  ];

  private array $permittedUpdateAttributes = [
    'parentDeviceId',
    'hardwareDetails',
    'technicalDetails',
    'restrictions',
    'locationDetails',
    'networkDetails',
    'accountDetails',
    'osDetails',
    'securityDetails',
    'androidConfigs',
    'appleConfigs',
    'installedApps',
    'miscellaneous',
  ];

  public function __construct(array $paramsEntity)
  {
    foreach ($paramsEntity as $key => $value) {
      $this->$key = $value;
      $this->valuesSet[] = $key;
    }
  }

    public function setDeviceId(int $deviceId): void
    {
        $this->deviceId = $deviceId;
        $this->valuesSet[] = 'deviceId'; // Assuming you want to mark deviceId as set
    }

  public function getCreationAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      $entity->$classAttribute = $this->$classAttribute;
    }
    foreach ($this->creationAttributes as $attributeToBePresent) {
      if (!isset($entity->$attributeToBePresent)) {
        throw new \Exception('MUST_SET_ALL_CREATION_ATTRIBUTES');
      }
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
      if (!isset($entity[$attributeToBePresent])) {
        throw new \Exception('MUST_SET_ALL_CREATION_ATTRIBUTES');
      }
    }
    return $entity;
  }

  public function getUpdateAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      if (!in_array($classAttribute, $this->permittedUpdateAttributes)) {
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      }
      $entity->$classAttribute = $this->$classAttribute;
    }

    return $entity;
  }

  public function getUpdateAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      if (!in_array($classAttribute, $this->permittedUpdateAttributes)) {
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      }
      $entity[$classAttribute] = $this->$classAttribute;
    }
    return $entity;
  }
}

