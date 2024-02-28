<?php

namespace App\Repositories;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;
use App\Repositories\IRepository;
use App\Services\DatabaseDataRetriever;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class UserRepository implements IRepository
{
  private string $table = 'db_users_mssp_d3tGkTEST.users';
  private string $tableAssociationWithTags = 'tags_users';
  private string $databaseMDM = "db_users_mssp_d3tGkTEST"; //TODO replace with user database connection

  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
  }

  /**
   * Retrieve a paginated list of records from the database.
   *
   * @param array $paginationOptions Pagination options. Syntax: ['page' => 0, 'rowsPerPage' => 10]
   * @param array $selectAttributes Attributes to select from the database.
   * @return DTORepositoryOutput
   */
  public function listPaginated(array $paginationOptions, array $selectAttributes = ['*']): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: $selectAttributes,
        pagination: $paginationOptions
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

  public function listUsersTagAssociationPaginated(array|null $paginationOptions, array $selectAttributes, array $whereConditions, array $joinConditions): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: $selectAttributes,
        where: $whereConditions,
        join: $joinConditions,
        pagination: $paginationOptions
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

  public function getUserById(int $id): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: ['*'],
        where: [['attribute' => 'id','operator' => '=','value' => $id]]
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(),singleItem: $outputDTO->getPayload()[0],executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function get(mixed $identifier): DTORepositoryOutput
  {
    //NOT USED
    return new DTORepositoryOutput(false, null);
  }

  /**
   * @inheritDoc
   */
  public function store(IEntity $newEntity): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $attributes = $newEntity->getAttributesAsArray();
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

  public function patch($id,$newParams): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: 'tags',
        where: [['attribute' => 'id', 'operator' => '=', 'value' => $id]],
        updates: ['tagName' => $newParams['name']],
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

  public function updateTagAssociationByID(int $tag_id, IEntity $newEntity): DTORepositoryOutput{
    try {
      // Start the database transaction
      DB::connection($this->databaseMDM)->beginTransaction();
      $startTime = microtime(true);


      //DELETE
      $deleteDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: $this->tableAssociationWithTags,
        where: [['attribute' => 'tag_id','operator' => '=','value' => $tag_id]],
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $retriever = app(DatabaseDataRetriever::class, ['parameters' => $deleteDTO]);
      $deletedDTO = $retriever->execute();

      //CREATE
      $attributes = $newEntity->getCreationAttributesAsArray();
      $insert = [];
      foreach ($attributes['user_id'] as $user_id){
        $insert[] = [
          'tag_id' => $attributes['tag_id'],
          'user_id' => $user_id,
        ];
      }
      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: '', //$this->tableAssociationWithTags,
        insert: $insert
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $retriever->updateDTO($inputDTO);
      $createdDTO = $retriever->execute();
      if(!$createdDTO->isSuccess())
        throw new \Exception('WRONG_QUERY_EXECUTED');

      // Commit the transaction if everything was successful
      DB::connection($this->databaseMDM)->commit();

      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);

      return new DTORepositoryOutput(success: $createdDTO->isSuccess(),items: $createdDTO->getPayload(),executionTime: $executionTime, statusCode: $createdDTO->getStatusCode());
    } catch (\Exception|\Throwable $e) {
      // An error occurred, rollback the transaction
      DB::connection($this->databaseMDM)->rollback();

      return new DTORepositoryOutput(false, null);
    }
  }
}
