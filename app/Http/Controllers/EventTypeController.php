<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventTypeController extends CrudController
{
    public function __construct()
    {
        $this->setModel('App\Models\EventType');
    }

  public function list($filters = [])
  {
    $check = $this->_setDbConnectionFromAuthUser();
    if(!$check)
      return 'error';
    $userAuthenticated = auth()->user()->load(['rolesUser']);
    $query = $this->_getModel()::on($this->_getDbConnection());
    //FILTER CASES
    if (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->eventTypeFilter)) {
      $query = $query->whereIn('event_types.value', $userAuthenticated->rolesUser->eventTypeFilter);
    }
    return $query->get();
  }
}
