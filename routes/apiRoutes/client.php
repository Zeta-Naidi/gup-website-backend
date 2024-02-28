<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'client', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  Route::get('/', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters'), true);
      $filters = Validator::make($filters, [
        'selectAttributes' => 'required|array',
        'selectAttributes.*' => Rule::in(['resellers.name as resellerName', 'clients.id', 'baseUrl', 'host', 'companyName', 'resellerId']),
        'resellerId' => 'integer|min:1|max:100000'
      ])->validate();
      $responseData = app(Controllers\ClientController::class)->list($filters);
      return response(['success' => true, 'payload' => $responseData]);
    }catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  /*  Route::post('/', function (Request $request) {
      try {
        $params = $request->post();
        (new Controllers\ClientController())->create($params);
        return response('ok');
      } catch (\Exception $e) {
        return response()->json($e->getMessage());
      }
    });*/
  Route::get('{id}', function ($id, Request $request) {
    try {
      if (is_numeric($id) && $id > 0){
        $client = app(Controllers\ClientController::class)->get($id);
        return response(['success' => true, 'payload' => $client]);
      }
      else
        throw new Exception("ID PARAMETER NOT VALID IN api/client/{id}");
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  // KEEP COMMENT Route::put('{id}', [\App\Http\Controllers\ClientController::class, 'update']);
  // KEEP COMMENT Route::delete('{id}', [\App\Http\Controllers\ClientController::class, 'delete']);
});
