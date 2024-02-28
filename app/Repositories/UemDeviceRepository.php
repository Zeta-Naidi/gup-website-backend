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
use Illuminate\Support\Facades\Log;
use PHPUnit\Event\Code\Throwable;

class UemDeviceRepository implements IRepository
{
  private string $table = 'devices';
  private string $secondTable = 'devicesdetails';
  //private string $tableAssociationWithTags = 'tags_devices';
  private string $databaseMDM = "testing_mdm_prova_d3tGk"; //TODO replace with user database connection

  /**
   * @param string $database
   * @return void
   */
  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
  }

  /**
   * Retrieve a paginated list of records from the database.
   *
   * @param array|null $pagination
   * @param array|null $filters
   * @return DTORepositoryOutput
   */
  public function list(array|null $pagination = [], array|null $filters = null): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);

      $where =[];
      //FILTER ON SERIAL OR NAME -----------------------------------------------------------------------------------------------------
      if (isset($filters["serialOrName"])) {
        $where =
          [
            [ 'attribute' => 'serialNumber', 'operator' => 'LIKE', 'value' => '%' . $filters["serialOrName"] . '%'],
            [ 'attribute' => 'deviceName', 'operator' => 'LIKE', 'value' => '%' . $filters["serialOrName"] . '%'],
          ];
      }

      //FILTER ON STATUS -------------------------------------------------------------------------------------------------------------
      if (isset($filters["status"])) {
        $where = [[ 'attribute' => 'enrollmentType', 'operator' => '<', 'value' => $filters["status"]]];
      }

      //FILTER ON ID -------------------------------------------------------------------------------------------------------------
      if (isset($filters["id"])) {
        $where = [[ 'attribute' => 'id', 'operator' => '=', 'value' => $filters["id"]]];
      }

      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: ['*'],
        where: $where,
        pagination: $pagination
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

  /**
   * @param array|null $paginationOptions
   * @param array $selectAttributes
   * @param array $whereConditions
   * @param array $joinConditions
   * @return DTORepositoryOutput
   */
  public function listDevicesTagAssociationPaginated(array|null $paginationOptions, array $selectAttributes, array $whereConditions, array $joinConditions): DTORepositoryOutput
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

  /**
   * @param int $id
   * @return DTORepositoryOutput
   */
  public function getDeviceById(int $id): DTORepositoryOutput
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

  /**
   * @param int $id
   * @param bool $identifier
   * @return DTORepositoryOutput
   */
  public function getMdmPush(int $id, bool $identifier): DTORepositoryOutput
  {
    // TODO: rivedere  mdmPushMagic, mdmPushDeviceToken
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: $identifier ? ['mdmPushMagic'] : ['mdmPushMagic', 'mdmPushDeviceToken'],
        where: $identifier ? [['attribute' => 'mdmPushDeviceToken','operator' => '=','value' => $id]] : [['attribute' => 'id','operator' => '=','value' => $id]],
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

  /**
   * @param int $id
   * @return DTORepositoryOutput
   */
  public function getDeviceDetailsById(int $id): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: ['*'],
        where: [['attribute' => 'id','operator' => '=','value' => $id]],
        join: [['type' => 'leftJoin', 'table' => $this->secondTable, 'first' => 'devices.id', 'operator' => '=']],
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

  /**
   * @param mixed $identifier
   * @return DTORepositoryOutput
   */
  public function get(mixed $identifier): DTORepositoryOutput
  {
    //NOT USED
    return new DTORepositoryOutput(false, null);
  }

  /**
   * @param IEntity $newEntity
   * @return DTORepositoryOutput
   */
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

  /**
   * @param IEntity $newEntity
   * @param IEntity $newSecondEntity
   * @return DTORepositoryOutput
   */
  public function storeWithDetails(IEntity $newEntity, IEntity $newSecondEntity): DTORepositoryOutput
    {
        try {
            $startTime = microtime(true);

            $attributes = $newEntity->getCreationAttributesAsArray();
            $inputDTO = new DTODatabaseCreateInput(
                database: $this->databaseMDM,
                table: $this->table,
                insert: $attributes
            );
            $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();

            if (!$outputDTO->isSuccess()) {
                return new DTORepositoryOutput(false, null);
            }

            $id = 5;

            $secondAttributes['deviceId'] = $id;
            $secondAttributes = $newSecondEntity->getCreationAttributesAsArray();
            $secondInputDTO = new DTODatabaseCreateInput(
                database: $this->databaseMDM,
                table: $this->secondTable,
                insert: $secondAttributes
            );
            $secondOutputDTO = app(DatabaseDataRetriever::class, ['parameters' => $secondInputDTO])->execute();

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            $secondOutput = $secondOutputDTO->getPayload();

            return new DTORepositoryOutput(success: true, items: [$outputDTO->getPayload(), $secondOutput], executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
        } catch (\Exception $e) {
            \App\Exceptions\CatchedExceptionHandler::handle($e);
            return new DTORepositoryOutput(false, null);
        }
    }

  /**
   * @param $id
   * @param $newParams
   * @return DTORepositoryOutput
   */
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

  /**
   * @param int $id
   * @return DTORepositoryOutput
   */
  public function delete(int $id): DTORepositoryOutput{
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: $this->table,
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

  /**
   * @param int $tag_id
   * @param IEntity $newEntity
   * @return DTORepositoryOutput
   * @throws \Throwable
   */
  public function updateTagAssociationByID(int $tag_id, IEntity $newEntity): DTORepositoryOutput{
    try {
      DB::connection($this->databaseMDM)->beginTransaction();

      // Start the database transaction
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
      foreach ($attributes['device_id'] as $device_id){
        $insert[] = [
          'tag_id' => $attributes['tag_id'],
          'device_id' => $device_id,
        ];
      }

      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: $this->tableAssociationWithTags,
        insert: $insert
      );
      /**
       * @var DatabaseDataRetriever $retriever
       */
      $retriever->updateDTO($inputDTO);
      $createdDTO = $retriever->execute();
      if(!$createdDTO->isSuccess())
        throw new \Exception('WRONG_QUERY_EXECUTED');

      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);

      DB::connection($this->databaseMDM)->commit();

      return new DTORepositoryOutput(success: $createdDTO->isSuccess(),items: $createdDTO->getPayload(),executionTime: $executionTime, statusCode: $createdDTO->getStatusCode());
    } catch (\Exception|\Throwable $e) {
      DB::connection($this->databaseMDM)->rollback();
      return new DTORepositoryOutput(false, null, null, null);
    }
  }

  /**
   * @param int $token
   * @return DTORepositoryOutput
   */
  public function getDeviceIdFromMdmPushDeviceToken(int $token): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: ['id'],
        where: [['attribute' => 'mdmPushDeviceToken','operator' => '=','value' => $token]]
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

  /**
   * @param int $deviceId
   * @return DTORepositoryOutput
   */
  public function removeAndroidSupervision(int $deviceId): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'deviceId','operator' => '=','value' => $deviceId]], // OR ['attribute' => 'parentDeviceId','operator' => '=','value' => $deviceId]
        updates: ['isEnrolled' => 0]
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

  /**
   * @param array $devicesTokensToUpdate
   * @return DTORepositoryOutput
   */
  public function updateTblBindings(array $devicesTokensToUpdate): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: 'tblBindings',
        where: [['attribute' => 'pushDeviceToken','operator' => 'IN','value' => $devicesTokensToUpdate]],
        updates: ['pushDeviceToken' => NULL]
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }
}
