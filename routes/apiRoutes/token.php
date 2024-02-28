<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'token', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  Route::post('/', function (Request $request) {
    try {
      $params = $request->validate([
        'abilities' => 'nullable|array',
        'abilities.*' => Rule::in(['mispEvents','syslogApplications','syslogEvents']),
        'expiresAt' => 'nullable|date_format:Y-m-d',
      ]);
      $result = app(Controllers\TokenController::class)->create($params);
      return response($result);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
  Route::get('/', function (Request $request) {
    try {
      $result = app(Controllers\TokenController::class)->getUserTokens();
      return response($result);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
});
