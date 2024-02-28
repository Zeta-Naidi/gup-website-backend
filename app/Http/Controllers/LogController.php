<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogController extends CrudController
{
  public function __construct()
  {
    $this->_setDbConnectionFromAuthUser();
  }

  public function list($filters = [])
  {
    try {

      $userAuthenticated = auth()->user()->load(['rolesUser']);
      $query = DB::connection("db_users_mssp_d3tGk")->table('access_logs');

      //FILTERS ----------------------------------------------------------------------------------------------------------------

      //Super admin will see all logs, admin users won't
      if ($userAuthenticated->levelAdmin <= 1) {
        $query = $query->where('username', '!=', env('SUPER_ADMIN_USERNAME'));

        if ($userAuthenticated->rolesUser->relationship == 'reseller') {
          if (empty($userAuthenticated->rolesUser->clientsFilter)) {
            $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
              ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
              ->pluck('id')
              ->toArray();
            $query = $query->where(function ($query) use ($clientList, $userAuthenticated) {
              $query = $query->where(function ($query) use ($userAuthenticated) {
                $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->relationshipIds) . "]', resellerIds)");
              });
              $query = $query->orWhere(function ($query) use ($clientList, $userAuthenticated) {
                $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $clientList) . "]',clientIds)");
              });
            });
          }
          else{
            $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->clientsFilter) . "]',clientIds)");
          }
        } else if ($userAuthenticated->rolesUser->relationship == 'client'){
          $query = $query->whereRaw("JSON_CONTAINS('[".implode(',', $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds)."]',clientIds)");
        }
        else if($userAuthenticated->rolesUser->relationship == 'distributor' && $userAuthenticated->rolesUser->clientsFilter){
          $query = $query->whereRaw("JSON_CONTAINS('[" .
            implode(',', $userAuthenticated->rolesUser->clientsFilter)
            . "]',`roles_users`.`relationshipIds`)");
        }
        else throw new \Exception('Relationship not valid');
      }

      if (isset($filters['logType'])) {
        $query = $query->where('logType', $filters['logType']);
      } else $query = $query->where('logType', 0);

      if (isset($filters["period"])) {
        $query = $query->whereBetween('createdAt', [$filters['period']['from'], $filters['period']['to']]);
      }

      if (isset($filters["type"])) {
        $query = $query->whereIn('type', $filters["type"]);
      }

      if (isset($filters["ip"])) {
        $query = $query->whereIn('ip', $filters["ip"]);
      }
      if (isset($filters["username"])) {
        $filterWithoutNull = array_filter($filters["username"], function ($element) {
          return !is_null($element);
        });
        $query = $query->where(function ($query) use ($filters, $filterWithoutNull) {
          if (count($filterWithoutNull) < count($filters["username"]))
            $query = $query->whereNull('username');
          if (!empty($filterWithoutNull))
            $query = $query->orWhereIn('username', $filterWithoutNull);
        });
      }
      //ORDER-GROUP BY ----------------------------------------------------------------------------------------------------------------
      if (isset($filters["orderBy"])) {
        foreach ($filters["orderBy"] as $orderByFilter) {
          $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
        }
      }
      if (isset($filters["groupBy"])) {
        foreach ($filters["groupBy"] as $groupByFilter) {
          $query = $query->groupBy($groupByFilter);
        }
      }
      //RETURN CASES ----------------------------------------------------------------------------------------------------------------
      if (isset($filters["paginate"])) {
        return $query->paginate(array_key_exists("rowsPerPage", $filters) ? (int)$filters["rowsPerPage"] : 15,
          ['*'], 'page', $filters["page"]);
      } else if (isset($filters["selectAttributes"]))
        return $query->select(join(',', $filters["selectAttributes"]))->get();
      else return $query->get();

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'GENERIC_ERROR', 500]);
    }
  }
  public function listAdmin($filters = [])
  {
    try {
      $query = DB::connection(config('database.default'))->table('access_logs');

      //FILTERS ----------------------------------------------------------------------------------------------------------------

      if (isset($filters["period"])) {
        $query = $query->whereBetween('createdAt', [$filters['period']['from'], $filters['period']['to']]);
      }

      if (isset($filters["type"])) {
        $query = $query->whereIn('type', $filters["type"]);
      }

      if (isset($filters["ip"])) {
        $query = $query->whereIn('ip', $filters["ip"]);
      }
      if (isset($filters["username"])) {
        $filterWithoutNull = array_filter($filters["username"], function ($element) {
          return !is_null($element);
        });
        $query = $query->where(function ($query) use ($filters, $filterWithoutNull) {
          if (count($filterWithoutNull) < count($filters["username"]))
            $query = $query->whereNull('username');
          if (!empty($filterWithoutNull))
            $query = $query->orWhereIn('username', $filterWithoutNull);
        });
      }
      //ORDER-GROUP BY ----------------------------------------------------------------------------------------------------------------
      if (isset($filters["orderBy"])) {
        foreach ($filters["orderBy"] as $orderByFilter) {
          $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
        }
      }
      if (isset($filters["groupBy"])) {
        foreach ($filters["groupBy"] as $groupByFilter) {
          $query = $query->groupBy($groupByFilter);
        }
      }
      //RETURN CASES ----------------------------------------------------------------------------------------------------------------

      //If superadmin fetch also logs from db users || https://stackoverflow.com/questions/27194651/union-queries-from-different-databases-in-laravel-query-builder

      if (isset($filters["paginate"])) {
        return $query->paginate(array_key_exists("rowsPerPage", $filters) ? (int)$filters["rowsPerPage"] : 15,
          ['*'], 'page', $filters["page"]);
      } else if (isset($filters["selectAttributes"]))
        return $query->select(join(',', $filters["selectAttributes"]))->get();
      else return $query->get();

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'GENERIC_ERROR', 500]);
    }
  }


}
