<?php

namespace App\Entities;
use Exception;

class UemDeviceEntity implements IEntity
{
  private string $deviceDetails;
  private int $parentDeviceId;
  private string $deviceName;
  private string $modelName;
  private string $macAddress;
  private string $meid;
  private string $udid;
  private string $vendorId;
  private string $osArchitecture;
  private string $osType;
  private string $osEdtion;
  private string $abbinationCode;
  private string $serialNumber;
  private string $imei;
  private bool $isDeleted;
  private string $phoneNumber;
  private string $assignedLicense;
  private bool $isAndroidOem;
  private bool $isOnline;
  private string $brand;
  private bool $hasAndroidPlayServices;
  private string $agentFlavor;
  private string $windowsAgentVersion;
  private string $configuration;
  private string $deviceEntity;
  private string $createdAt;

  private array $valuesSet = [];

  // HAS TO BE PRESENT FOR CREATION ENTITY
  private array $creationAttributes = [
    'parentDeviceId',
    'deviceName',
    'modelName',
    'macAddress',
    'meid',
    'udid',
    'vendorId',
    'osArchitecture',
    'osType',
    'osEdition',
    'abbinationCode',
    'serialNumber',
    'imei',
    'isDeleted',
    'phoneNumber',
    'assignedLicense',
    'isAndroidOem',
    'isOnline',
    'brand',
    'hasAndroidPlayServices',
    'agentFlavor',
    'windowsAgentVersion',
    'configuration',
    'deviceEntity',
    'createdAt',
  ];

  // POSSIBLE ATTRIBUTES FOR UPDATE
  private array $permittedUpdateAttributes = [
    'parentDeviceId',
    'deviceName',
    'modelName',
    'macAddress',
    'meid',
    'udid',
    'vendorId',
    'osArchitecture',
    'osType',
    'osEdition',
    'abbinationCode',
    'serialNumber',
    'imei',
    'isDeleted',
    'phoneNumber',
    'assignedLicense',
    'isAndroidOem',
    'isOnline',
    'brand',
    'hasAndroidPlayServices',
    'agentFlavor',
    'windowsAgentVersion',
    'configuration',
    'deviceEntity',
    'createdAt',
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
    foreach ($this->creationAttributes as $attributeToBePresent){
      if(!isset($entity->$attributeToBePresent))
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
    foreach ($this->creationAttributes as $attributeToBePresent){
      if(!isset($entity[$attributeToBePresent]))
        throw new \Exception('MUST_SET_ALL_CREATION_ATTRIBUTES');
    }
    return $entity;
  }
  public function getUpdateAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      if(!in_array($classAttribute,$this->permittedUpdateAttributes))
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity->$classAttribute = $this->$classAttribute;
    }

    return $entity;
  }

  public function getUpdateAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      if(!in_array($classAttribute,$this->permittedUpdateAttributes))
        throw new \Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity[$classAttribute] = $this->$classAttribute;
    }
    return $entity;
  }
}
