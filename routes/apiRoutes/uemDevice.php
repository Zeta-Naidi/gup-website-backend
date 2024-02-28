<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\UemDeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

Route::group(['prefix' => 'uem/device', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','mdmUser']], function () {
//Route::group(['prefix' => 'uem/device'], function () {
  Route::get('/', function (Request $request) {
    try {
      //$filters = json_decode($request->query('filters') ?? [], true);
      $parameters =  $request->validate([
        'serialOrName' => 'nullable|string|min:4|max:64',
        'status' => 'nullable|integer',
        'rowsPerPage' => 'nullable|integer',
        'page' => 'nullable|integer'
        ]);
      /**
       * @var UemDeviceController $responseData
       */
      $controller = app(Controllers\UemDeviceController::class);
      $controllerResponse = $controller->list($parameters);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::get('{id}', function ($id, Request $request) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|numeric|min:1',
      ])->validate();
      $controllerResponse = app(Controllers\UemDeviceController::class)->getDeviceById($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::post('create', function (Request $request) {
      try {
          $params = $request->validate([
            'deviceName' => 'required|string|max:200',
            'parentDeviceId' => 'required|integer',
            'enrollmentType' => 'required|integer',
            'modelName' => 'nullable|string|max:250',
            'macAddress' => 'nullable|string|max:50',
            'meid' => 'nullable|string|max:14',
            'osType' => 'nullable|string|max:10',
            'osEdition' => 'nullable|string|max:50',
            'osVersion' => 'nullable|string|max:50',
            'udid' => 'nullable|string|max:100',
            'vendorId' => 'nullable|string|max:100',
            'osArchitecture' => 'nullable|string|max:100',
            'abbinationCode' => 'nullable|string|max:100',
            'mdmDeviceId' => 'nullable|integer',
            'manufacturer' => 'nullable|string|max:250',
            'serialNumber' => 'nullable|string|max:100',
            'imei' => 'nullable|string|max:150',
            'isDeleted' => 'nullable|boolean',
            'phoneNumber' => 'nullable|string|max:50',
            'isOnline' => 'nullable|boolean',
            'brand' => 'nullable|string|max:250',
            'networkIdentity' => 'nullable|array',
            'configuration' => 'nullable|array',
            'deviceDetails' => 'nullable|array',
            "deviceIdentity" => 'nullable|array',
          ]);
        $controllerResponse = app(Controllers\UemDeviceController::class)->create($params);
        return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
        } catch (\Exception $e) {
            CatchedExceptionHandler::handle($e);
            return response(["success" => false, "message" => "SERVER_ERROR"], 500);
        }
    });
  Route::group(['prefix' => 'details'], function () {
    Route::get('/', function (Request $request) {
      //TODO:
    });
    Route::get('{id}', function ($id, Request $request) {
      try {
        Validator::make(["id" => $id], [
          'id' => 'required|numeric|min:1',
        ])->validate();
        $deviceDetailsById = app(Controllers\UemDeviceController::class)->getDeviceDetailsById($id);
        return response(["success" => true, "payload" => $deviceDetailsById]);
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
      }
    });
  });
  // KEEP COMMENT Route::put('{id}', [\App\Http\Controllers\ClientController::class, 'update']);
  // KEEP COMMENT Route::delete('{id}', [\App\Http\Controllers\ClientController::class, 'delete']);
});
