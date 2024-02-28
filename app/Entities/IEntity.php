<?php

namespace App\Entities;

use stdClass;

interface IEntity
{
  /**
   * @return stdClass with the attributes keyAttribute = valueAttribute
   */
  public function getCreationAttributesAsObject(): stdClass;

  /**
   * @return array with keyAttribute => valueAttribute
   */
  public function getCreationAttributesAsArray(): array;

  public function getUpdateAttributesAsObject(): stdClass;

  /**
   * @return array with keyAttribute => valueAttribute
   */
  public function getUpdateAttributesAsArray(): array;
}
