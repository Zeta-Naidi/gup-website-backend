<?php

namespace App\Repositories;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;
use App\Entities\TagEntity;
use App\Services\DatabaseDataRetriever;
use Illuminate\Contracts\Auth\Authenticatable;

class TagRepository implements IRepository
{
  private string $table = 'tags';
  private string $databaseMDM = "testing_mdm_prova1_d3tGkTEST"; //TODO replace with user database connection

  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
  }

  /**
   * Retrieve a paginated list of records from the database.
   *
   * @param array|null $paginationOptions Pagination options. Syntax: ['page' => 0, 'rowsPerPage' => 10]
   * @param array $selectAttributes Attributes to select from the database.
   * @return DTORepositoryOutput
   */
  public function listPaginated(?array $paginationOptions, array $selectAttributes = ['*']): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: $selectAttributes,
        pagination: $paginationOptions
      );
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = $databaseRetriever->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(),items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function store(IEntity $newEntity): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $attributes = $newEntity->getCreationAttributesAsArray();
      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: $this->table,
        insert: $attributes
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(),items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function get(): DTORepositoryOutput
  {
    //NOT USED
    return new DTORepositoryOutput(false, null);
  }

  public function patch($id,$newParams): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: 'tags',
        where: [['attribute' => 'id', 'operator' => '=', 'value' => $id]],
        updates: ['tagName' => $newParams['tagName']],
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(),items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function delete(int $id): DTORepositoryOutput{
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: 'tags',
        where: [['attribute' => 'id','operator' => '=','value' => $id]],
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(),items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }
}
