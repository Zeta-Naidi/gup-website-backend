<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends CrudController
{
  public function __construct()
  {
    $this->setModel('App\Models\Client');
  }

  public function list($filters = [])
  {
    try {
      $userAuthenticated = auth()->user()->load(['rolesUser']);
      $check = $this->_setDbConnectionFromAuthUser();
      if (!$check)
        return 'error';

      $query = $this->_getModel()::on($this->_getDbConnection());

      if (!empty($userAuthenticated->rolesUser->clientsFilter) || $userAuthenticated->rolesUser->relationship != 'distributor') {
        $clientList = [];
        if ($userAuthenticated->rolesUser->relationship == 'reseller') {
          $clientList = DB::connection($this->_getDbConnection())->table('clients')
            ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
            ->pluck('id')
            ->toArray();
        } else if ($userAuthenticated->rolesUser->relationship == 'client') {
          $clientList = $userAuthenticated->rolesUser->relationshipIds;
        }
        //Stricter filter
        if (!empty($userAuthenticated->rolesUser->clientsFilter)) {
          $clientList = $userAuthenticated->rolesUser->clientsFilter;
        }
      }
      if (!empty($clientList))
        $query = $query->whereIn('clients.id', $clientList);

      if (isset($filters["selectAttributes"]))
        $query = $query->select(...$filters["selectAttributes"]);

      $query = $query->join('resellers', 'resellers.id', 'clients.resellerId');

      return $query->get();
    } catch
    (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }

  }

}
