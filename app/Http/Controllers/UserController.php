<?php

namespace App\Http\Controllers;

use App\Exceptions\ParametersException;
use App\Jobs\LogAccess;
use App\Models\Client;
use App\Models\User;
use App\Security\BlockedHostsHandler;
use GPBMetadata\Google\Api\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserController extends CrudController
{
  public function __construct()
  {
    $this->setModel('App\Models\User');
    $this->_setDbConnection(config('database.default'));
  }

  public function list($filters = [])
  {
    try {
      $userAuthenticated = auth()->user();
      $query = $this->_getModel()::on($this->_getDbConnection())
        ->with('rolesUser')
        ->where('distributor_id', $userAuthenticated->distributor_id)
        ->whereNot('levelAdmin', '>', 1) //Not display super admin
        ->join('roles_users', 'roles_users.userId', '=', 'users.id')
        ->select('users.*');
      if ($userAuthenticated->levelAdmin <= 1) {
        if ($userAuthenticated->rolesUser->relationship == 'reseller') {
          if (empty($userAuthenticated->rolesUser->clientsFilter)) {
            $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
              ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
              ->pluck('id')
              ->toArray();
            $query = $query->where(function ($query) use ($clientList, $userAuthenticated) {
              $query = $query->where(function ($query) use ($userAuthenticated) {
                $query = $query->where('roles_users.relationship', 'reseller');
                $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->relationshipIds) . "]',`roles_users`.`relationshipIds`)");
              });
              $query = $query->orWhere(function ($query) use ($clientList, $userAuthenticated) {
                $query = $query->where('roles_users.relationship', 'client');
                $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $clientList) . "]',`roles_users`.`relationshipIds`)");
              });
            });
          } else {
            $query = $query->where('roles_users.relationship', 'client');
            $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->clientsFilter) . "]',`roles_users`.`relationshipIds`)");
          }
        }
        else if ($userAuthenticated->rolesUser->relationship == 'client') {
          $query = $query->where('roles_users.relationship', 'client');
          $query = $query->whereRaw("JSON_CONTAINS('[" .
            implode(',', $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds)
            . "]',`roles_users`.`relationshipIds`)");
        }
        else if($userAuthenticated->rolesUser->relationship == 'distributor' && $userAuthenticated->rolesUser->clientsFilter){
          $query = $query->where('roles_users.relationship', 'client');
          $query = $query->whereRaw("JSON_CONTAINS('[" .
            implode(',', $userAuthenticated->rolesUser->clientsFilter)
            . "]',`roles_users`.`relationshipIds`)");
        }
        else throw new \Exception('Relationship not valid');
      }
      // PAGINATION
      if (isset($filters["paginate"])) {
        if (isset($filters['orderBy'])) {
          foreach ($filters['orderBy'] as $orderByFilter) {
            if (isset($orderByFilter['attribute']) && isset($orderByFilter['order'])) {
              $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
            }
          }
        }
        $rowsPerPage = (int)$filters['rowsPerPage'] ?? 1;
        $page = (int)$filters['page'] ?? 1;
        $items = $query->paginate($rowsPerPage, ['*'], 'page', $page);
        $transformedItems = $items->getCollection()->transform(fn($item) => $this->transformItem($item));
        $items->setCollection($transformedItems);
      } else {
        $items = $query->get()->transform(fn($item) => $this->transformItem($item));
      }
      return response(['success' => true, 'payload' => $items]);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  }

  public function create($params): array
  {
    try {
      $authenticatedUser = auth()->user()->load('rolesUser');

      $relationship = $authenticatedUser->rolesUser->relationship;

      //Check if params are valid based on relationships
      if (!$this->checkHierarchy($relationship, $params["userRelationship"], $params["userRelationshipIds"])) {
        BlockedHostsHandler::blockIp(app()->request->ip(), 'TRIED_TO_CREATE_USER_WITH_RELATIONSHIPS_NOT_LINKED_TO_AUTHENTICATED_USER');
        BlockedHostsHandler::blockUser($authenticatedUser->username, 'TRIED_TO_CREATE_USER_WITH_RELATIONSHIPS_NOT_LINKED_TO_AUTHENTICATED_USER');
        throw new ParametersException('Relationship not valid with user relationships');
      }

      $paramsFiltered = [
        "username" => $params["username"],
        "email" => $params["email"],
        "password" => Hash::make($params["password"], ["rounds" => 14]),
        "userRelationship" => $params["userRelationship"],
        "userRelationshipIds" => $params["userRelationshipIds"],
        "accessLogsPermission" => $params["accessLogsPermission"],
        "systemLogsPermission" => $params["systemLogsPermission"],
        "configurationPermission" => $params["configurationPermission"],
        "iamPermission" => $params["iamPermission"],
        "clientsFilter" => empty($params["clientsFilter"]) ? null : $params["clientsFilter"],
        "scoreFilter" => empty($params["scoreFilter"]) ? null : $params["scoreFilter"],
        "modFilter" => empty($params["modFilter"]) ? null : $params["modFilter"],
        "eventTypeFilter" => empty($params["eventTypeFilter"]) ? null : $params["eventTypeFilter"],
      ];
      $isUsernameAlreadyPresent = User::where('username', $paramsFiltered["username"])->first();
      $isEmailAlreadyPresent = User::where('email', $paramsFiltered["email"])->first();

      if (isset($isUsernameAlreadyPresent) || isset($isEmailAlreadyPresent))
        return ["success" => false, "message" => isset($isEmailAlreadyPresent) ? "EMAIL_ALREADY_PRESENT" : "USERNAME_ALREADY_PRESENT"];

      $userCreated = User::create([
        "username" => $paramsFiltered["username"],
        "email" => $paramsFiltered["email"],
        "password" => $paramsFiltered["password"],
        "levelAdmin" => 0,
        "nameDatabaseConnection" => $authenticatedUser->nameDatabaseConnection,
        "distributor_id" => $authenticatedUser->distributor_id,
        "companyName" => $authenticatedUser->companyName,
      ]);
      DB::connection(config('database.default'))->table('roles_users')->insert([
        "userId" => $userCreated->id,
        "relationship" => $paramsFiltered["userRelationship"],
        "relationshipIds" => json_encode($paramsFiltered["userRelationshipIds"]),
        "accessLogsPermission" => $paramsFiltered["accessLogsPermission"],
        "systemLogsPermission" => $paramsFiltered["systemLogsPermission"],
        "configurationPermission" => $paramsFiltered["configurationPermission"],
        "iamPermission" => $paramsFiltered["iamPermission"],
        "clientsFilter" => !empty($paramsFiltered["clientsFilter"]) ? json_encode($paramsFiltered["clientsFilter"]) : null,
        "scoreFilter" => !empty($paramsFiltered["scoreFilter"]) ? json_encode($paramsFiltered["scoreFilter"]) : null,
        "modFilter" => !empty($paramsFiltered["modFilter"]) ? $paramsFiltered["modFilter"] : null,
        "eventTypeFilter" => !empty($paramsFiltered["eventTypeFilter"]) ? json_encode($paramsFiltered["eventTypeFilter"]) : null,
      ]);
      dispatch(new LogAccess(
        from: [
          "username" => $authenticatedUser->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: null, type: "CREATE_USER", value: ['username' => $paramsFiltered["username"], 'email' => $paramsFiltered["email"]], distributorId: $authenticatedUser->distributor_id, timestamp: new \DateTime(),
        rolesUser: $authenticatedUser->rolesUser
      ));
      return ["success" => true, "message" => "USER_CREATED"];
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return ["success" => false, "message" => "SERVER_ERROR"];
    }
  }

  /**
   * @param $params
   */
  public function updateWithUserRoles($params)
  {
    try {
      $authenticatedUser = auth()->user()->load('rolesUser');
      $paramsFiltered = [
        "id" => $params["id"],
        "username" => $params["username"],
        "email" => $params["email"],
        "userRelationship" => $params["userRelationship"],
        "userRelationshipIds" => $params["userRelationshipIds"],
        "accessLogsPermission" => $params["accessLogsPermission"],
        "systemLogsPermission" => $params["systemLogsPermission"],
        "configurationPermission" => $params["configurationPermission"],
        "iamPermission" => $params["iamPermission"],
        "clientsFilter" => empty($params["clientsFilter"]) ? null : $params["clientsFilter"],
        "scoreFilter" => empty($params["scoreFilter"]) ? null : $params["scoreFilter"],
        "modFilter" => empty($params["modFilter"]) ? null : $params["modFilter"],
        "eventTypeFilter" => empty($params["eventTypeFilter"]) ? null : $params["eventTypeFilter"],
      ];
      $isUpdated = User::where("id", $paramsFiltered["id"])
        ->whereNot('levelAdmin', '>', 1) //Not display super admin
        ->where('distributor_id', auth()->user()->distributor_id)
        ->update([
          "username" => $paramsFiltered["username"],
          "email" => $paramsFiltered["email"],
        ]);
      //CASE WHERE USER TRY TO UPDATE USER OF ANOTHER DISTRIBUTOR OR SUPERADMIN TODO Better block
      if (!$isUpdated)
        throw new \Exception('User Not Updated');

      DB::connection(config('database.default'))->table('roles_users')->where('userId', $paramsFiltered["id"])
        ->update([
          "relationship" => $paramsFiltered["userRelationship"],
          "relationshipIds" => json_encode($paramsFiltered["userRelationshipIds"]),
          "accessLogsPermission" => $paramsFiltered["accessLogsPermission"],
          "systemLogsPermission" => $paramsFiltered["systemLogsPermission"],
          "configurationPermission" => $paramsFiltered["configurationPermission"],
          "iamPermission" => $paramsFiltered["iamPermission"],
          "clientsFilter" => !empty($paramsFiltered["clientsFilter"]) ? json_encode($paramsFiltered["clientsFilter"]) : null,
          "scoreFilter" => !empty($paramsFiltered["scoreFilter"]) ? json_encode($paramsFiltered["scoreFilter"]) : null,
          "modFilter" => !empty($paramsFiltered["modFilter"]) ? $paramsFiltered["modFilter"] : null,
          "eventTypeFilter" => !empty($paramsFiltered["eventTypeFilter"]) ? json_encode($paramsFiltered["eventTypeFilter"]) : null,
        ]);

      dispatch(new LogAccess(
        from: [
          "username" => app()->request->user()->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: ["username" => $paramsFiltered["username"], "id" => $paramsFiltered["id"]],
        type: "UPDATE_USER", value: ['username' => $paramsFiltered["username"], 'email' => $paramsFiltered["email"]], distributorId: app()->request->user()->distributor_id, timestamp: new \DateTime(),
        rolesUser: $authenticatedUser->rolesUser
      ));
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR', 500]);
    }
  }

  public function delete($id): Response
  {
    try {
      $authenticatedUser = auth()->user()->load('rolesUser');
      $userDeleted = $this->_getModel()::on($this->_getDbConnection())
        ->where('id', $id)
        ->where('distributor_id', auth()->user()->distributor_id)
        ->select(['username'])
        ->first();
      $this->_getModel()::on($this->_getDbConnection())->where('id', $id)
        ->whereNot('levelAdmin', '>', 1) //No super admin
        ->where('distributor_id', auth()->user()->distributor_id)
        ->delete();
      dispatch(new LogAccess(
        from: [
          "username" => app()->request->user()->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: ["id" => $id], type: "DELETE_USER", value: ['username' => $userDeleted->username, 'email' => $userDeleted->email], distributorId: app()->request->user()->distributor_id, timestamp: new \DateTime(),
        rolesUser: $authenticatedUser->rolesUser
      ));
      return response(['success' => true, 'message' => 'USER_DELETED']);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  private function checkHierarchy(string $currentUserRole, string $targetRole, array $targetIds): bool
  {
    $hierarchy = [
      'distributor' => ['distributor', 'reseller', 'client'],
      'reseller' => ['reseller', 'client'],
      'client' => ['client'],
    ];
    $userAuthenticated = auth()->user()->load('rolesUser');
    if (!in_array($targetRole, $hierarchy[$currentUserRole]))
      return false;
    //hierarchy correct, to check ids are valid
    if ($userAuthenticated->levelAdmin <= 1) {
      if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        if ($targetRole == 'client') {
          if (empty($userAuthenticated->rolesUser->clientsFilter)) {
            $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
              ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
              ->pluck('id')
              ->toArray();
          } else $clientList = $userAuthenticated->rolesUser->clientsFilter;

          foreach ($targetIds as $targetId) {
            if (!in_array($targetId, $clientList))
              return false;
          }
        } else if ($targetRole == 'reseller') {
          if (!empty($userAuthenticated->rolesUser->clientsFilter))
            return false;
          $possibleResellerIds = $userAuthenticated->rolesUser->relationshipIds;
          foreach ($targetIds as $targetId) {
            if (!in_array($targetId, $possibleResellerIds))
              return false;
          }
        } else throw new \Exception('Relationship not valid');
      } else if ($userAuthenticated->rolesUser->relationship == 'client') {
        $possibleClientIds = $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds;
        foreach ($targetIds as $targetId) {
          if (!in_array($targetId, $possibleClientIds))
            return false;
        }
      }
      //TODO Add distributor user with clientsFilter
      else return false;
    }
    return true; // if algorithm arrives here, params are valid
  }

  private function transformItem($item)
  {
    return [
      "id" => $item->id,
      "email" => $item->email,
      "username" => $item->username,
      "companyName" => $item->companyName,
      "relationship" => $item->rolesUser->relationship,
      "levelAdmin" => $item->levelAdmin,
      "rolesUser" => $item->rolesUser,
      "isMfaActive" => isset($item->otpKey)
    ];
  }
}
