<?php

namespace App\Http\Controllers;

use App\Dtos\Controller\ControllerResponse;
use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Entities\TagEntity;
use App\Entities\TagsDevicesEntity;
use App\Entities\TagsUsersEntity;
use App\Repositories\TagRepository;
use App\Repositories\UemDeviceRepository;
use App\Repositories\UserRepository;
use App\Services\DatabaseDataRetriever;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
  public function list(array $pagination = null): ControllerResponse
  {
    try {
      /**
       * @var TagRepository $repository
       */
      $repository = app(TagRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listPaginated = $repository->listPaginated(paginationOptions: $pagination);
      return $listPaginated->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function add(array $params): ControllerResponse
  {
    try {
      $newTag = new TagEntity($params);
      /**
       * @var TagRepository $repository
       */
      $repository = app(TagRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      return $repository->store($newTag)->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }

  }

  public function patch(int $id, array $newParams): ControllerResponse
  {
    try {
      /**
       * @var TagRepository $repository
       */
      $repository = app(TagRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      return $repository->patch($id,$newParams)->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function delete(int $id): ControllerResponse
  {
    try {
      /**
       * @var TagRepository $repository
       */
      $repository = app(TagRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      return $repository->delete($id)->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  /**
   * Retrieve tagged data associated with users or devices.
   *
   * This method retrieves data based on the specified type ('users' or 'devices').
   * @param string $type
   * @param array $pagination
   * @return ControllerResponse
   */
  public function tagAssociation(string $type, array $pagination = []): ControllerResponse
  {
    try {
      if($type === 'users'){
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $listPaginated = $repository->listPaginated(paginationOptions: $pagination);
        return $listPaginated->formatControllerResponse();
      }else if($type === 'devices'){
        /**
         * @var UemDeviceRepository $repository
         */
        $repository = app(UemDeviceRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $listPaginated = $repository->list(pagination: $pagination);
        return $listPaginated->formatControllerResponse();
      }else{
        \App\Exceptions\CatchedExceptionHandler::handle("tag Association type not correct");
        return new ControllerResponse(false, null, 500);
      }

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function associationByID(int $id, string $type, array|null $pagination = []): ControllerResponse
  {
    try {
      if($type === 'users'){
        $whereCondition = [[ 'attribute' => 'db_users_mssp_d3tGkTEST.users.id', 'operator' => '=', 'value' => $id]];
        $joinConditions = [['type' => 'Join', 'table' => 'tags_users', 'first' => 'db_users_mssp_d3tGkTEST.users.id', 'operator' => '=', 'second' => 'tags_users.user_id']]; // different database
        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $listPaginated = $repository->listUsersTagAssociationPaginated(paginationOptions: $pagination, selectAttributes: ['db_users_mssp_d3tGkTEST.users.id'], whereConditions: $whereCondition, joinConditions:$joinConditions);
        return $listPaginated->formatControllerResponse();
      }else if($type === 'devices'){
        $whereCondition = [[ 'attribute' => 'devices.id', 'operator' => '=', 'value' => $id]];
        $joinConditions = [['type' => 'Join', 'table' => 'tags_devices', 'first' => 'devices.id', 'operator' => '=', 'second' => 'tags_devices.device_id']];
        /**
         * @var UemDeviceRepository $repository
         */
        $repository = app(UemDeviceRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $listPaginated = $repository->listDevicesTagAssociationPaginated(paginationOptions: $pagination, selectAttributes: ['devices.id'], whereConditions: $whereCondition, joinConditions:$joinConditions);
        return $listPaginated->formatControllerResponse();
      }else{
        \App\Exceptions\CatchedExceptionHandler::handle("tag Association type not correct");
        return new ControllerResponse(false, null, 500);
      }

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function updateTagAssociationByID(int $tag_id, string $type, array $ids): ControllerResponse
  {
    try {
      if($type === 'users'){
        $dataToInsert = [
          'tag_id' => $tag_id,
          'user_id' => $ids,
        ];
        $newTagsDevices = new TagsUsersEntity($dataToInsert);

        /**
         * @var UserRepository $repository
         */
        $repository = app(UserRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $result = $repository->updateTagAssociationByID(tag_id: $tag_id, newEntity:$newTagsDevices);
        return $result->formatControllerResponse();
      }
      else if($type === 'devices'){
        $dataToInsert = [
          'tag_id' => $tag_id,
          'device_id' => $ids,
        ];
        $newTagsDevices = new TagsDevicesEntity($dataToInsert);

        /**
         * @var UemDeviceRepository $repository
         */
        $repository = app(UemDeviceRepository::class);
        $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
        $result = $repository->updateTagAssociationByID(tag_id: $tag_id, newEntity:$newTagsDevices);
        return $result->formatControllerResponse();
       }else{
        \App\Exceptions\CatchedExceptionHandler::handle("tag Association type not correct");
        return new ControllerResponse(false, null, 500);
      }

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

}
