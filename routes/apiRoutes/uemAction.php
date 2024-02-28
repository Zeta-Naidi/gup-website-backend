<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\ActionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::group(['prefix' => 'uem/action', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts']], function () {
  Route::get('user/{id}', function ($id) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|integer|min:1|max:1000000',
      ])->validate();

      /**
       * @var ActionController $controller
       */
      $controller = app(ActionController::class);
      $controllerResponse = $controller->getActionsListUser($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);

    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

  Route::get('device/{id}', function ($id) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|integer|min:1|max:10000000',
      ])->validate();

      /**
       * @var ActionController $controller
       */
      $controller = app(ActionController::class);
      $controllerResponse = $controller->getActionsListDevice($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);

    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

  Route::post('execute', function (Request $request) {
    try {
      $params = $request->validate([
        'actionIdentifier' => 'required|string|max:200',
        'deviceId' => 'required|integer|min:1|max:10000000',
        'actionFormContent' => 'nullable|array',
      ]);

      /**
       * @var ActionController $controller
       */
      $controller = app(ActionController::class);
      $controllerResponse = $controller->execute($params['actionIdentifier'], $params['deviceId'], $params['actionFormContent']);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);

    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

});
