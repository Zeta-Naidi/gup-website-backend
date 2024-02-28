<?php

namespace App\Repositories;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseOutput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;
use App\Entities\PayloadEntity;
use App\Services\DatabaseDataRetriever;
use Illuminate\Support\Facades\Log;

class UemPayloadRepository implements IRepository
{

  private string $table = 'payloads';
  private string $databaseMDM = ""; // testing_mdm_prova_d3tGk

  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
  }

  /**
   * @inheritDoc
   */
  public function store(IEntity $newPayload): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $attributes = $newPayload->getCreationAttributesAsArray();
      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: $this->table,
        insert: $attributes
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      if (!$outputDTO->isSuccess())
        throw new \Exception('CANT_CREATE_PAYLOAD');
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), singleItem: $outputDTO->getPayload()[0], executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }

  }

  public function get(mixed $identifier): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'id', 'operator' => '=', 'value' => $identifier]],
      );
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = $databaseRetriever->execute();
      $profile = !empty($outputDTO->getPayload()) ? $outputDTO->getPayload()[0] : null;
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), singleItem: $profile, executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function getProfilePayloads(int $profileId): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
      );
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = $databaseRetriever->execute();
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), items: $outputDTO->getPayload(), executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function deleteProfilePayloads(int $profileId, $profileVersion): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
      );
      /**
       * @var DatabaseDataRetriever $databaseRetriever
       */
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      $outputDTO = $databaseRetriever->execute();

      foreach ($outputDTO->getPayload() as $payloadToDelete) {
        $inputDTO = new DTODatabaseCreateInput(
          database: $this->databaseMDM,
          table: 'oldPayloads',
          insert: [
            'payloadUUID' => $payloadToDelete->payloadUUID,
            'profileId' => $payloadToDelete->profileId,
            'payloadDisplayName' => $payloadToDelete->payloadDisplayName,
            'payloadDescription' => $payloadToDelete->PayloadDescription ?? null,
            'payloadOrganization' => $payloadToDelete->PayloadOrganization ?? null,
            'applePayloadType' => $payloadToDelete->applePayloadType,
            'params' => $payloadToDelete->params,
            'config' => $payloadToDelete->config,
            'payloadVersion' => $payloadToDelete->payloadVersion,
            'profileVersion' => $profileVersion,
            'createdAt' => $payloadToDelete->createdAt
          ]
        );
        $databaseRetriever->updateDTO($inputDTO);
        $result = $databaseRetriever->execute();
        if (!$result->isSuccess())
          throw new \Exception('CANT_DELETE_PAYLOAD');
      }

      $inputDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
      );
      $databaseRetriever->updateDTO($inputDTO);
      $databaseRetriever->execute();

      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), items: $outputDTO->getPayload(), executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function update(int $payloadId, PayloadEntity $payloadToUpdate, bool $handleArchivePayload = false): DTORepositoryOutput{
    try {
      $startTime = microtime(true);
      $inputDTO = new DTODatabaseUpdateInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'id','operator' => '=', 'value' => $payloadId]],
        updates: $payloadToUpdate->getUpdateAttributesAsArray()
      );
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO])->execute();
      if (!$outputDTO->isSuccess())
        throw new \Exception('CANT_UPDATE_PAYLOAD');
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: $outputDTO->isSuccess(), executionTime: $executionTime, statusCode: $outputDTO->getStatusCode());
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

}
