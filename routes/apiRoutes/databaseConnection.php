<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'databaseConnection', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'isSuperAdmin', 'blockedHosts','siemUser']], function () {
  Route::get('/', function (Request $request) {
    try {
      // Not used for now
      //$filters = $request->query('filters');
      $responseData = app(Controllers\DatabaseConnectionController::class)->list();
      return response(['success' => true, 'payload' => ["distributors" => $responseData, "users" => config('database.database')]]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
  Route::post('/', function (Request $request) {
    try {
      $params = $request->validate([
        "distributor" => "required|array",
        "distributor.user_id" => "required", //TODO datatype
        "distributor.companyName" => "required", //TODO datatype
        //connection TODO
      ]);
      $result = app(Controllers\DatabaseConnectionController::class)->create($params);
      return response(["success" => (bool)$result, 'payload' => $result]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::post('/migrate', function (Request $request) {
    try {
      $params = $request->validate([
        'databaseName' => 'required|string|max:64',
        'mod' => 'required|in:up,down'
      ]);
      $result = app(Controllers\DatabaseConnectionController::class)->migrateDatabase($params["databaseName"], $params["mod"]);
      return response(["success" => $result]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::post('/createMDM', function (Request $request) {
    try {
      $params = $request->validate([
        'databaseName' => 'required|string|max:64'
      ]);
      $result = app(Controllers\DatabaseConnectionController::class)->createMDM($params["databaseName"]);
      return response(["success" => $result]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::post('/migrateMDM', function (Request $request) {
    try {
      $params = $request->validate([
        'databaseName' => 'required|string|max:64'
      ]);
      $result = app(Controllers\DatabaseConnectionController::class)->migrateMDMDatabase($params["databaseName"]);
      return response(["success" => $result]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::get('chimpaDistributors', [Controllers\DatabaseConnectionController::class, 'listChimpaDistributors']);
  Route::put('setDistributorToUser', function (Request $request) {
    try {
      $params = $request->validate([
        'connection' => 'required|string|max:64'
      ]);
      if (isset($params["connection"])) {
        $nameDatabaseConnection = $params["connection"];
        $distributor_id = strpos($nameDatabaseConnection,'_distributor_') ? explode("_", $nameDatabaseConnection)[2] : null;
      } else
        return response(["success" => false]);
      $result = app(UserController::class)->update(auth()->user()->id, ["nameDatabaseConnection" => $nameDatabaseConnection, "distributor_id" => $distributor_id]);
      return response(["success" => true, 'payload' => $result]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
});
