<?php

namespace App\Entities;
class TagEntity implements IEntity
{
  private string $tagName;
  private array $valuesSet = [];

  // HAS TO BE PRESENT FOR CREATION ENTITY
  private array $creationAttributes = ['tagName'];

  // POSSIBLE ATTRIBUTES FOR UPDATE
  private array $permittedUpdateAttributes = ['tagName'];

  public function __construct(array $paramsEntity)
  {
    foreach ($paramsEntity as $key => $value) {
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
