<?php

use App\Dtos\Controller\ControllerResponse;
use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'tag', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','mdmUser']], function () {
//Route::group(['prefix' => 'tag'], function () {
  Route::get('/', function (Request $request) {
    try {
      $pagination = $request->validate([
        'page' => 'nullable|integer|min:1',
        'rowsPerPage' => 'nullable|integer|min:10|max:150',
      ]);
      /**
       * @var TagController $controller
       */
      $controller =  app(TagController::class);
      $controllerResponse = $controller->list($pagination);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::post('add', function (Request $request) {
    try {
      // validator for name - check if correct
      $params = $request->validate([
        'tagName' => 'required|string|min:1|max:128'
      ]);
      $paramsFiltered = [
        'tagName' => $params['tagName']
      ];
        /**
       * @var TagController $controller
       */
      $controller = app(TagController::class);
      $controllerResponse = $controller->add($paramsFiltered);
      //echo json_encode($controllerResponse);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::put('update/{id}', function ($id, Request $request) {
    try {
      Validator::make(['id' => $id], [
        'id' => 'required|integer|min:1',
      ])->validate(); // it throws an exception if it's not correct

      $params = $request->validate([
        'tagName' => 'required|string|min:1|max:128',
      ]);

      $controllerResponse = app(TagController::class)->patch($id, $params);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::delete('delete/{id}', function ($id) {
    try {
      Validator::make(['id' => $id], [
        'id' => 'required|integer|min:1',
      ])->validate(); // it throws an exception if it's not correct

      $controllerResponse = app(TagController::class)->delete($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::get('tagAssociation', function (Request $request) {
    try {
      $params = $request->validate([
        'type' => 'required|string|min:1|max:128'
      ]);

      $controllerResponse = app(TagController::class)->tagAssociation($params);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::post('associationByID', function (Request $request) {
    try {
      $params = $request->validate([
        'type' => 'required|string|min:1|max:128',
        'id' => 'required|integer|min:1',
        'pagination' => 'nullable',
      ]);
      $controllerResponse = app(TagController::class)->associationByID($params['id'], $params['type'], $params['pagination'] ?? []);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

  Route::put('updateTagAssociationByID', function (Request $request) {
    try {
      $params = $request->validate([
        'type' => 'required|string|min:1|max:128',
        'tag_id' => 'required|integer|min:1',
        'ids' => 'nullable|array|min:0'
      ]);
      $controllerResponse = app(TagController::class)->updateTagAssociationByID($params['tag_id'], $params['type'], $params['ids']);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  })->middleware();

});
