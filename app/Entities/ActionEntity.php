<?php

namespace App\Entities;
use Exception;
use Illuminate\Support\Facades\Log;

class ActionEntity implements IEntity
{
  private string $type;
  private string|null $params;
  private string $status;
  private string|null $errorDetail;
  private string $sentAt;
  private string $executedAt;

  private array $valuesSet = [];

  // HAS TO BE PRESENT FOR CREATION ENTITY
  private array $creationAttributes = ['type', 'status', 'createdAt'];

  // POSSIBLE ATTRIBUTES FOR UPDATE
  private array $permittedUpdateAttributes = ['type', 'params', 'status', 'errorDetail', 'sentAt', 'executedAt'];

  public function __construct(array $paramsEntity)
  {
    foreach ($paramsEntity as $key => $value) {
      $this->$key = $value;
      $this->valuesSet[] = $key;
    }
  }

  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
  public function getCreationAttributesAsArray(): array
  {
    $entity = [];
    foreach ($this->valuesSet as $classAttribute) {
      $entity[$classAttribute] = $this->$classAttribute;
    }
    foreach ($this->creationAttributes as $attributeToBePresent){
      if(!isset($entity[$attributeToBePresent]))
        throw new Exception('MUST_SET_ALL_CREATION_ATTRIBUTES_');
    }
    return $entity;
  }

  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
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
