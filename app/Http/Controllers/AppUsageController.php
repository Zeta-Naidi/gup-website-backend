<?php

namespace App\Http\Controllers;

class AppUsageController extends CrudController
{
  public function __construct()
  {
    $this->setModel('App\Models\AppUsage');
  }
  public function list($filters=[]){
    try{
      $check = $this->_setDbConnectionFromAuthUser();
      $dataToReturn = [];

      if (!$check)
        return response(['message' => 'GENERIC_ERROR', 500]);

      $query = $this->_getModel()::on($this->_getDbConnection());


      if(isset($filters["serialNumbers"])){
        if (!$filters["serialNumbers"]["include"])
          $query = $query->whereNotIn('deviceSerialNumber', $filters["serialNumbers"]["items"]);
        else
          $query = $query->whereIn('deviceSerialNumber', $filters["serialNumbers"]["items"]);
      }

      if (isset($filters["clientIds"]))
        $query = $query->whereIn("clientId", $filters["clientIds"]);

      if (isset($filters["period"])) {
        $query = $query->whereBetween('firstTimestamp', [$filters['period']['from'], $filters['period']['to']]);
      }

      $query = $query->get();
      $dataToReturn["rows"] = $query->toArray();
      return $dataToReturn;

    }catch (\Exception $e){
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR', 500]);
    }
  }
}
