<?php

namespace App\Repositories;

use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;

interface IRepository
{
  /**
   * Store data in the repository.
   *
   * @param  IEntity $newEntity
   * @return DTORepositoryOutput
   */
  public function store(IEntity $newEntity):DTORepositoryOutput;

  /**
   * Get data from the repository.
   *
   * @param  mixed  $identifier
   * @return mixed
   */
  public function get(mixed $identifier):DTORepositoryOutput;

  public function setConnectionToDatabase(string $database);
}
