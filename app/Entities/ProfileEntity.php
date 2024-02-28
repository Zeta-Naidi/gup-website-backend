<?php

namespace App\Entities;
use Exception;
use Illuminate\Support\Facades\Log;

class ProfileEntity implements IEntity
{
  private string $name;
  private string $profileDescription;
  private string $profileType;
  private string $profileUUID;
  private  $profileExpirationDate;
  private  $removalDate;
  private  $durationUntilRemoval;
  private  $durationUntilRemovalDate;
  private  $consentText;
  private  $profileRemovalDisallowed;
  private  $profileScope;
  private  $profileOrganization;
  private  $isEncrypted;
  private  $profileVersion;
  private  $onSingleDevice;
  private  $limitOnDates;
  private  $limitOnWifiRange;
  private  $limitOnPublicIps;
  private  $home;
  private  $copeMaster;
  private  $enabled;
  private  $profileChanges;
  private  $operatingSystem;
  private string $createdAt;

  private array $valuesSet = [];

  // HAS TO BE PRESENT FOR CREATION ENTITY
  private array $creationAttributes = [
    'profileDisplayName',
    'profileDescription',
    'profileType',
    'profileUUID',
    'profileRemovalDisallowed',
    'isEncrypted',
    'profileVersion',
    'onSingleDevice',
    'home',
    'copeMaster',
    'enabled',
    'operatingSystem',
    'createdAt',
  ];

  // POSSIBLE ATTRIBUTES FOR UPDATE
  private array $permittedUpdateAttributes = [
    'profileDisplayName',
    'profileDescription',
    'profileType',
    'profileUUID',
    'profileExpirationDate',
    'removalDate',
    'durationUntilRemoval',
    'durationUntilRemovalDate',
    'consentText',
    'profileRemovalDisallowed',
    'profileScope',
    'profileOrganization',
    'isEncrypted',
    'profileVersion',
    'onSingleDevice',
    'limitOnDates',
    'limitOnWifiRange',
    'limitOnPublicIps',
    'home',
    'copeMaster',
    'enabled',
    'profileChanges',
    'operatingSystem',
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
        throw new Exception('MUST_SET_ALL_CREATION_ATTRIBUTES');
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
        throw new Exception('MUST_SET_ALL_CREATION_ATTRIBUTES_'.$attributeToBePresent);
    }
    return $entity;
  }
  public function getUpdateAttributesAsObject(): \stdClass
  {
    $entity = new \stdClass();
    foreach ($this->valuesSet as $classAttribute) {
      if(!in_array($classAttribute,$this->permittedUpdateAttributes))
        throw new Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity->$classAttribute = $this->$classAttribute;
    }

    return $entity;
  }
  public function getUpdateAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      if(!in_array($classAttribute,$this->permittedUpdateAttributes))
        throw new Exception('ATTRIBUTE_NOT_POSSIBLE_TO_UPDATE');
      $entity[$classAttribute] = $this->$classAttribute;
    }
    return $entity;
  }
}

