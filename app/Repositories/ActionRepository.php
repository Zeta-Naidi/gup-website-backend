<?php

namespace App\Repositories;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;
use App\Services\DatabaseDataRetriever;

class ActionRepository implements IRepository
{
  private string $table = 'actions';
  private string $databaseMDM = "";

  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
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

  public function get(mixed $identifier): DTORepositoryOutput
  {
    return new DTORepositoryOutput(false, null);
  }

  public function getActionsNotSent(): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'sentAt', 'operator' => 'NOT', 'value' => null]],
        groupBy: ['deviceId']
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

  public function updateAppleCheckins(int $deviceId): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'deviceId', 'operator' => '=', 'value' => $deviceId], ['attribute' => 'status', 'operator' => '=', 'value' => 0]],
        updates: ['lastPushSent' => now()] // 'pushAttempts' => $newPushAttempts
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

  public function updateWindowsCheckins(int $id, int $msgId, int $cmdId, int $sessionId): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'id', 'operator' => '=', 'value' => $id]],
        updates: ['msgId' => $msgId, 'cmdId'=>$cmdId, 'sessionId'=>$sessionId]
      );
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = $databaseRetriever->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), items: $outputDTO->getPayload(),executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }
}
