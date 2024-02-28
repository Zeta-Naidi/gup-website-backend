<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'eventType', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  Route::get('/', function (Request $request) {
    try {
      //Not used for now
      //$filters = json_decode($request->query('filters'),true);
      $responseData = app(\App\Http\Controllers\EventTypeController::class)->list();
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
  //Route::get('{id}', [\App\Http\Controllers\EventTypeController::class, 'get']);
});
