<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'device', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {

  Route::get('/', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'serialOrName' => 'nullable|string|min:4|max:64',
        'selectAttributes' => 'required|array',
        'selectAttributes.*' => Rule::in(['name','serialNumber','id']),
        ])->validate();
      $responseData = app(Controllers\DeviceController::class)->list($filters);
      return response(["success" => true, "payload" => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"],500);
    }
  });
  /*
 KEEP COMMENT - FOR NOW NOT USED
   Route::post('', function (Request $request) {
     try {
       $params = $request->post();
       (new Controllers\DeviceController())->create($params);
       return response('ok');
     } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
       CatchedExceptionHandler::handle($e);
       return response(["success" => false, "message" => "SERVER_ERROR"],500);
     }
   });
   Route::get('{id}', [\App\Http\Controllers\DeviceController::class, 'get']);
   Route::put('{id}', [\App\Http\Controllers\DeviceController::class, 'update']);
   Route::delete('{id}', [\App\Http\Controllers\DeviceController::class, 'delete']);*/
  Route::get('securityPosture/{serialNumber}', function (Request $request, $serialNumber){
    try {
      Validator::make(["serialNumber" => $serialNumber], [
        'serialNumber' => 'required|regex:/^[a-zA-Z0-9\-]+$/|max:96',
      ])->validate();
      $securityPosture = app(Controllers\DeviceController::class)->getSecurityPosture($serialNumber);
      return response(["success" => true, "payload" => $securityPosture]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (Exception $e){
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::get('apps/{serialNumber}', function (Request $request, $serialNumber){
    try {
      Validator::make(["serialNumber" => $serialNumber], [
        'serialNumber' => 'required|regex:/^[a-zA-Z0-9\-]+$/|max:96',
      ])->validate();
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'ignoreAppServices' => 'nullable|boolean',
      ])->validate();
      $installedApps = app(Controllers\DeviceController::class)->getInstalledApps($serialNumber,$filters);
      return response(["success" => true, "payload" => $installedApps]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (Exception $e){
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::get('groups', function (Request $request){
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'clients' => 'required|array',
        'clients.*' => 'integer|min:1'
      ])->validate();
      $groups = app(Controllers\DeviceController::class)->getDeviceGroups($filters);
      return response(["success" => true, "payload" => $groups]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (Exception $e){
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
});
