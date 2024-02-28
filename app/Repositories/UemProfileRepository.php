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
use App\Entities\ProfileEntity;
use App\Models\Payload;
use App\Services\DatabaseDataRetriever;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PharIo\Manifest\ElementCollectionException;
use Throwable;

class UemProfileRepository implements IRepository
{

  private string $table = 'profiles';
  private string $databaseMDM = ""; // testing_mdm_prova_d3tGk

  public function setConnectionToDatabase(string $database): void
  {
    $this->databaseMDM = $database;
  }

  public function store(IEntity $newProfile): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      $attributes = $newProfile->getCreationAttributesAsArray();
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
        throw new \Exception('CANT_CREATE_PROFILE');
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

  /**
   * Retrieve paginated data with payloads from the database.
   *
   * @param array|null $paginationOptions Pagination options (e.g., ['page' => 1, 'rowsPerPage' => 10]).
   * @param array $selectAttributes Attributes to select from the database (default is all attributes).
   * @param array|null $whereConditions Conditions for filtering the data. (e.g., [[ 'attribute' => 'name', 'operator' => =|!=|in|notIn|isNull|like|isNotNull|between, 'value' => $value|[$v1,$v2...,$vn]]]))
   * @param array|null $orderByConditions Conditions for filtering the data. (e.g., [ column => 'asc'|'desc', ... => ....]))
   *
   * @return DTORepositoryOutput            A Data Transfer Object representing the output of the repository function.
   */
  public function listPaginatedWithPayloads(?array $paginationOptions,array  $selectAttributes = ['*'],?array $whereConditions = null,?array $orderByConditions = ['id' => 'desc'],?array $filters = [],): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);

      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: $this->table,
        select: $selectAttributes,
        where: $whereConditions,
        orderBy: $orderByConditions,
        pagination: $paginationOptions
      );
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      /**
       * @var DTODatabaseOutput $outputDTO
       */
      $outputDTO = $databaseRetriever->execute();
      $additionalInfos = $outputDTO->getAdditionalInfos();
      $profilesWithPayloads = [];
      foreach ($outputDTO->getPayload() as $profile) {
        $inputDTO = new DTODatabaseSelectInput(
          database: $this->databaseMDM,
          table: 'payloads',
          where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profile->id]],
        );
        $databaseRetriever->updateDTO($inputDTO);
        $outputDTO = $databaseRetriever->execute();
        $profile->payloadList = $outputDTO->getPayload();
        $profilesWithPayloads [] = $profile;
      }
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: true, items: $profilesWithPayloads, executionTime: $executionTime, statusCode: 200, additionalInfos: $additionalInfos);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  public function delete(int $profileId): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      DB::connection($this->databaseMDM)->beginTransaction();

      $repoResponse = $this->get($profileId);
      ($repoResponse->isSuccess() && !empty($repoResponse->getSingleItem()))
        ? $profile = $repoResponse->getSingleItem()
        : throw new \Exception('PROFILEID_NOT_VALID');

      /**
       * @var UemPayloadRepository $payloadRepository
       */
      $payloadRepository = app(UemPayloadRepository::class);
      $payloadRepository->setConnectionToDatabase($this->databaseMDM);
      $payloadRepoResponse = $payloadRepository->deleteProfilePayloads($profileId, $profile->profileVersion);
      if (!$payloadRepoResponse->isSuccess())
        throw new \Exception('CANT_DELETE_PAYLOADS');

      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: 'oldprofiles',
        insert: [
          'profileId' => $profile->id,
          'profileUUID' => $profile->profileUUID,
          'profileDisplayName' => $profile->profileDisplayName,
          'profileDescription' => $profile->profileDescription,
          'operatingSystem' => $profile->operatingSystem,
          'profileType' => $profile->profileType,
          'profileExpirationDate' => $profile->profileExpirationDate,
          'removalDate' => $profile->removalDate,
          'durationUntilRemoval' => $profile->durationUntilRemoval,
          'durationUntilRemovalDate' => $profile->durationUntilRemovalDate,
          'consentText' => $profile->consentText,
          'profileRemovalDisallowed' => $profile->profileRemovalDisallowed,
          'profileScope' => $profile->profileScope,
          'profileOrganization' => $profile->profileOrganization,
          'isEncrypted' => $profile->isEncrypted,
          'profileVersion' => $profile->profileVersion,
          'onSingleDevice' => $profile->onSingleDevice,
          'limitOnDates' => $profile->limitOnDates,
          'limitOnWifiRange' => $profile->limitOnWifiRange,
          'limitOnPublicIps' => $profile->limitOnPublicIps,
          'home' => $profile->home,
          'copeMaster' => $profile->copeMaster,
          'enabled' => $profile->enabled,
          'profileChanges' => json_encode([
            'value' => true,
            'action' => 'deleteProfile',
          ]),
          'createdAt' => $profile->createdAt
        ]
      );
      /**
       * @var DatabaseDataRetriever $databaseRetriever
       */
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      $result = $databaseRetriever->execute();
      if (!$result->isSuccess())
        throw new \Exception('CANT_CREATE_OLD_PROFILE');

      $inputDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: $this->table,
        where: [['attribute' => 'id', 'operator' => '=', 'value' => $profileId]],
      );
      $databaseRetriever->updateDTO($inputDTO);
      $result = $databaseRetriever->execute();
      if (!$result->isSuccess())
        throw new \Exception('CANT_DELETE_PROFILE');

      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      DB::connection($this->databaseMDM)->commit();
      return new DTORepositoryOutput(success: true, executionTime: $executionTime, statusCode: 200);
    } catch (\Exception|\Throwable $e) {
      DB::connection($this->databaseMDM)->rollBack();
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  /**
   * @param ProfileEntity $newProfile
   * @param array $payloads
   * @return DTORepositoryOutput
   * @throws Throwable
   */
  public function storeWithPayloads(ProfileEntity $newProfile, array $payloads): DTORepositoryOutput
  {
    try {
      $startTime = microtime(true);
      DB::connection($this->databaseMDM)->beginTransaction();

      $repoResponse = $this->store($newProfile);

      if (!$repoResponse->isSuccess())
        throw new \Exception('CANT_CREATE_PROFILE');

      $profileId = $repoResponse->getSingleItem();
      /**
       * @var UemPayloadRepository $payloadRepo
       */
      $payloadRepo = app(UemPayloadRepository::class);
      $payloadRepo->setConnectionToDatabase($this->databaseMDM);

      foreach ($payloads as $payload) {
        $payloadToCreate = new PayloadEntity([
          'payloadUUID' => $payload['payloadUUID'],
          'profileId' => $profileId,
          'payloadDisplayName' => $payload['PayloadName'],
          'payloadDescription' => $payload['payloadDescription'] ?? '',
          'payloadOrganization' => $payload['payloadOrganization'] ?? '',
          'applePayloadType' => $payload['applePayloadType'],
          'params' => $payload['params'] ?? null,
          'config' => $payload['config'] ?? null,
          'payloadVersion' => $payload['payloadVersion'],
          'createdAt' => $payload['createdAt'],
        ]);
        $repoResponse = $payloadRepo->store($payloadToCreate);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PAYLOADS');
      }

      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      DB::connection($this->databaseMDM)->commit();

      return new DTORepositoryOutput(success: true, executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      DB::connection($this->databaseMDM)->rollBack();
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  /**
   * @param int $profileId
   * @param ProfileEntity $profileToUpdate
   * @param array $payloads
   * @return DTORepositoryOutput
   * @throws Throwable
   */
  public function update(int $profileId, ProfileEntity $profileToUpdate, array $payloads = []): DTORepositoryOutput
  {
    try {
      //TODO TO FINISH
      $startTime = microtime(true);
      DB::connection($this->databaseMDM)->beginTransaction();

      //----------- GET CURRENT PROFILE ----------------------------
      $repoResponse = $this->get($profileId);
      ($repoResponse->isSuccess() && !empty($repoResponse->getSingleItem()))
        ? $profile = $repoResponse->getSingleItem()
        : throw new \Exception('PROFILEID_NOT_VALID');

      //----------- ARCHIVE PROFILE AND PAYLOADS ----------------------------
      $archiveResponse = $this->archiveProfileWithPayloads($profileId);
      if (!$archiveResponse->isSuccess())
        throw new \Exception('CANT_ARCHIVE_IN_UPDATE_PROFILE');

      /**
       * @var DatabaseDataRetriever $databaseRetriever
       */
      $databaseRetriever = app(DatabaseDataRetriever::class,
        ['parameters' => new DTODatabaseSelectInput(database: $this->databaseMDM, table: $this->table)]
      );

      //----------- DELETE CURRENT PAYLOADS IN TABLE ----------------------------
      // delete the "old" payloads that has been stored in the oldPayloads table
      $inputDTO = new DTODatabaseDeleteInput(
        database: $this->databaseMDM,
        table: 'payloads',
        where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
      );
      $databaseRetriever->updateDTO($inputDTO);
      $deleteResult = $databaseRetriever->execute();
      if (!$deleteResult->isSuccess())
        throw new \Exception('CANT_DELETE_PAYLOAD_IN_UPDATE_PROFILE');

      //----------- GET CURRENT PAYLOADS ----------------------------
      /**
       * @var UemPayloadRepository $payloadRepository
       */
      $payloadRepository = app(UemPayloadRepository::class);
      $payloadRepository->setConnectionToDatabase($this->databaseMDM);
      $result = $payloadRepository->getProfilePayloads($profileId);
      if (!$result->isSuccess())
        throw new \Exception('CANT_GET_PAYLOADS_IN_UPDATE_PROFILE');
      $currentPayloads = $result->getItems();
      $changes = [];

      //----------- CHECK DELETED PAYLOADS ----------------------------
      foreach ($currentPayloads as $currentPayload) {
        $isPayloadDeleted = in_array($currentPayload->payloadDisplayName, array_column($payloads, 'payloadDisplayName'));
        if ($isPayloadDeleted) {
          $changes[] = [
            'value' => true,
            'action' => 'delete',
            'payloadName' => $currentPayload['payloadDisplayName'],
          ];
          $inputDTO = new DTODatabaseDeleteInput(
            database: $this->databaseMDM,
            table: 'payloads',
            where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
          );
          $databaseRetriever->updateDTO($inputDTO);
          $deleteResult = $databaseRetriever->execute();
          if (!$deleteResult->isSuccess())
            throw new \Exception('CANT_DELETE_PAYLOAD_IN_UPDATE_PROFILE');
        }
      }

      //----------- CHECK UPDATED OR CREATED PAYLOADS ----------------------------
      foreach ($payloads as $newPayload) {
        $newPayload['payloadUUID'] = $newPayload['payloadUUID'] ?? 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC6';
        $params = [];

        foreach ($newPayload['Fields'] as $field) {
          $params[] = [
            'id' => $field['id'],
            'value' => $field['value'],
          ];
        }
        $newPayload['Fields'] = json_encode($params);
        $position = array_search($newPayload['PayloadName'], array_column($currentPayloads, 'PayloadName'));
        $isPayloadCreated = $position === false;

        if (!$isPayloadCreated) {
          //----------- UPDATE PAYLOAD ----------------------------
          $currentPayload = $currentPayloads[$position];
          //TODO HANDLE DIFFERENCE PARAMS PAYLOADS
          //$changes[] = $this->checkChanges($currentPayloads[$position], $payloadItem['Fields'], $payloadItem['PayloadName']);

          $payloadToUpdate = new PayloadEntity([
            'payloadDisplayName' => $newPayload['PayloadName'],
            'payloadDescription' => $newPayload['PayloadDescription'] ?? $currentPayload->payloadDescription,
            'payloadOrganization' => $newPayload['PayloadOrganization'] ?? $currentPayload->payloadOrganization,
            'applePayloadType' => $newPayload['applePayloadType'] ?? $currentPayload->applePayloadType,
            'params' => $newPayload['Fields'],
            'config' => $newPayload['config'],
            'payloadVersion' => $newPayload->payloadVersion + 1,
            'createdAt' => $newPayload['createdAt'],
          ]);
          $result = $payloadRepository->update($currentPayload->id,$payloadToUpdate);
          if (!$result->isSuccess())
            throw new \Exception('CANT_UPDATE_PAYLOAD_IN_UPDATE_PROFILE');
        } else {
          $changes[] = [
            'value' => true,
            'action' => 'create',
            'payloadName' => $newPayload['PayloadName'],
          ];

          $payloadToStore = new PayloadEntity([
            'payloadUUID' => $newPayload['payloadUUID'] ?? 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC6',
            'profileId' => $profile->id,
            'payloadDisplayName' => $newPayload['PayloadName'],
            'payloadDescription' => $newPayload['PayloadDescription'] ?? '',
            'payloadOrganization' => $newPayload['PayloadOrganization'] ?? '',
            'applePayloadType' => $newPayload['applePayloadType'] ?? 'com.apple.payloads',
            'params' => json_decode($newPayload['params']),
            'config' => $newPayload['config'],
            'payloadVersion' => 1,
            'createdAt' => $newPayload['createdAt'],
          ]);
          $result = $payloadRepository->store($payloadToStore);
          if (!$result->isSuccess())
            throw new \Exception('CANT_STORE_PAYLOAD_IN_UPDATE_PROFILE');
        }
      }

      //TODO UPDATE PROFILE_CHANGES IN OLD_PROFILE
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      DB::connection($this->databaseMDM)->commit();
      return new DTORepositoryOutput(success: true, executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      DB::connection($this->databaseMDM)->rollBack();
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

  private function archiveProfileWithPayloads($profileId): DTORepositoryOutput
  {
    try {
      //TRANSACTION NOT USED BECAUSE HANDLED IN OTHER FUNCTIONS
      $startTime = microtime(true);
      //----------- GET CURRENT PROFILE ----------------------------
      $repoResponse = $this->get($profileId);
      ($repoResponse->isSuccess() && !empty($repoResponse->getSingleItem()))
        ? $profile = $repoResponse->getSingleItem()
        : throw new \Exception('PROFILEID_NOT_VALID');

      $profileToArchive = new ProfileEntity([
        'profileId' => $profile->id,
        'profileUUID' => $profile->profileUUID,
        'profileDisplayName' => $profile->profileDisplayName,
        'profileDescription' => $profile->profileDescription,
        'operatingSystem' => $profile->operatingSystem,
        'profileType' => $profile->profileType,
        'profileExpirationDate' => $profile->profileExpirationDate,
        'removalDate' => $profile->removalDate,
        'durationUntilRemoval' => $profile->durationUntilRemoval,
        'durationUntilRemovalDate' => $profile->durationUntilRemovalDate,
        'consentText' => $profile->consentText,
        'profileRemovalDisallowed' => $profile->profileRemovalDisallowed,
        'profileScope' => $profile->profileScope,
        'profileOrganization' => $profile->profileOrganization,
        'isEncrypted' => $profile->isEncrypted,
        'profileVersion' => $profile->profileVersion,
        'onSingleDevice' => $profile->onSingleDevice,
        'limitOnDates' => $profile->limitOnDates,
        'limitOnWifiRange' => $profile->limitOnWifiRange,
        'limitOnPublicIps' => $profile->limitOnPublicIps,
        'home' => $profile->home,
        'copeMaster' => $profile->copeMaster,
        'enabled' => $profile->enabled,
        'profileChanges' => null,
        'createdAt' => $profile->createdAt,
      ]);

      $inputDTO = new DTODatabaseCreateInput(
        database: $this->databaseMDM,
        table: 'oldprofiles',
        insert: $profileToArchive->getCreationAttributesAsArray()
      );
      /**
       * @var DatabaseDataRetriever $databaseRetriever
       */
      $databaseRetriever = app(DatabaseDataRetriever::class, ['parameters' => $inputDTO]);
      $result = $databaseRetriever->execute();

      if (!$result->isSuccess())
        throw new \Exception('CANT_ARCHIVE_PROFILE');

      $inputDTO = new DTODatabaseSelectInput(
        database: $this->databaseMDM,
        table: 'payloads',
        where: [['attribute' => 'profileId', 'operator' => '=', 'value' => $profileId]],
      );
      $databaseRetriever->updateDTO($inputDTO);
      $result = $databaseRetriever->execute();
      if (!$result->isSuccess())
        throw new \Exception('CANT_GET_PAYLOADS');

      foreach ($result->getPayload() as $payloadToArchive) {
        $inputDTO = new DTODatabaseCreateInput(
          database: $this->databaseMDM,
          table: 'oldPayloads',
          insert: [
            'payloadUUID' => $payloadToArchive->payloadUUID,
            'profileId' => $payloadToArchive->profileId,
            'payloadDisplayName' => $payloadToArchive->payloadDisplayName,
            'payloadDescription' => $payloadToArchive->PayloadDescription ?? null,
            'payloadOrganization' => $payloadToArchive->PayloadOrganization ?? null,
            'applePayloadType' => $payloadToArchive->applePayloadType,
            'params' => $payloadToArchive->params,
            'config' => $payloadToArchive->config,
            'payloadVersion' => $payloadToArchive->payloadVersion,
            'profileVersion' => $profile->profileVersion,
            'createdAt' => $payloadToArchive->createdAt,
          ]
        );
        $databaseRetriever->updateDTO($inputDTO);
        $result = $databaseRetriever->execute();

        if (!$result->isSuccess())
          throw new \Exception('CANT_ARCHIVE_PAYLOADS');
      }
      $endTime = microtime(true);
      $executionTime = round(($endTime - $startTime) * 1000, 2);
      return new DTORepositoryOutput(success: true,executionTime: $executionTime, statusCode: 200);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new DTORepositoryOutput(false, null);
    }
  }

}
