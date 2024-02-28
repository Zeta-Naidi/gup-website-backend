<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\UemProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
Route::group(['prefix' => 'uem/profile', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts']], function () {
  Route::get('/', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? '{}', true);
      $filters = Validator::make($filters, [
        'paginate' => 'nullable|boolean',
        'rowsPerPage' => 'nullable|integer',
        'page' => 'nullable|integer',
        'operatingSystem' => 'nullable|string', // |in:Apple,Microsoft,Android,Mixed
        'startDate' => 'nullable|string',
        'endDate' => 'nullable|string',
      ])->validate();
      $controllerResponse = app(Controllers\UemProfileController::class)->list($filters);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::get('{id}', function ($id) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|integer',
      ])->validate();
      $controllerResponse = app(Controllers\UemProfileController::class)->getProfileById($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::delete('{id}', function ($id) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|integer|min:1|max:1000000',
      ])->validate();
      $controllerResponse = app(Controllers\UemProfileController::class)->delete($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::post('create', function (Request $request) {
    try {
      $params = $request->validate([
        'name' => 'required|string|max:255',
        'operatingSystem' => 'required|string|max:255',
        'description' => 'required|string',
        'devices' => 'array',
        'users' => 'array',
        'tags' => 'array',
        'datetime' => 'required|string|date_format:H:i:s d-m-Y',
        'payloadList' => 'required|array',
        'payloadList.*.PayloadName' => 'required|string|max:255',
        'payloadList.*.icon' => 'required|string|url',
        'payloadList.*.config.show' => 'required|boolean',
        'payloadList.*.config.title' => 'nullable|string|max:255',
        'payloadList.*.config.description' => 'nullable|string',
        'payloadList.*.config.img' => 'nullable|string|url',
        'payloadList.*.osCategorized' => 'nullable|boolean',
        'payloadList.*.Fields' => 'required|array',
        'payloadList.*.Fields.*.id' => 'required|integer',
        'payloadList.*.Fields.*.os' => 'nullable',
        'payloadList.*.Fields.*.label' => 'required|string|max:255',
        'payloadList.*.Fields.*.field_id' => 'required|string|max:255',
        'payloadList.*.Fields.*.value' => 'nullable',
        'payloadList.*.Fields.*.input_type' => 'required|string|max:255',
        'payloadList.*.Fields.*.description' => 'nullable|string',
        'payloadList.*.Fields.*.options' => 'array',
      ]);

      $params['profileUUID'] = $request->input('profileUUID', 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC5');
      $params['profileType'] = $request->input('profileType', 'Configuration');
      $params['profileRemovalDisallowed'] = $request->input('profileRemovalDisallowed', 0);
      $params['isEncrypted'] = $request->input('profileRemovalDisallowed', 0);
      $params['profileVersion'] = $request->input('profileVersion', 1);
      $params['onSingleDevice'] = $request->input('onSingleDevice', 0);
      $params['home'] = $request->input('home', 0);
      $params['copeMaster'] = $request->input('copeMaster', 0);
      $params['enabled'] = $request->input('enabled', 1);

      $controllerResponse = app(Controllers\UemProfileController::class)->create($params);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    }  catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
  Route::post('loadfromfile', function (Request $request) {
    try {
      $params = $request->validate([
        'profileDisplayName' => 'required|string|max:255',
        'operatingSystem' => 'required|string|max:255',
        'profileDescription' => 'required|string',
        'createdAt' => 'required|string|date_format:Y-m-d H:i:s',
        'payloadList' => 'required|array',
        'payloadList.*.payloadDisplayName' => 'required|string|max:255',
        'payloadList.*.osCategorized' => 'nullable|boolean',
        'payloadList.*.params' => 'required|string',
        'payloadList.*.config' => 'required|string',
      ]);

      $params['profileUUID'] = $request->input('profileUUID', 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC5');
      $params['profileType'] = $request->input('profileType', 'Configuration');
      $params['profileRemovalDisallowed'] = $request->input('profileRemovalDisallowed', 0);
      $params['isEncrypted'] = $request->input('profileRemovalDisallowed', 0);
      $params['profileVersion'] = $request->input('profileVersion', 1);
      $params['onSingleDevice'] = $request->input('onSingleDevice', 0);
      $params['home'] = $request->input('home', 0);
      $params['copeMaster'] = $request->input('copeMaster', 0);
      $params['enabled'] = $request->input('enabled', 1);

      $controllerResponse = app(Controllers\UemProfileController::class)->loadfromfile($params);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    }  catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
  Route::put('update/{id}', function (Request $request, $id) {
    try {
      $params = $request->validate([
        'profileDisplayName' => 'required|string|max:255',
        'profileDescription' => 'required|string|max:255',
        'operatingSystem' => 'required|string|max:255',
        'devices' => 'array',
        'users' => 'array',
        'tags' => 'array',
        'datetime' => 'required|string|date_format:H:i:s d-m-Y',
        'payloadList' => 'required|array',
        'payloadList.*.PayloadName' => 'required|string|max:255',
        'payloadList.*.icon' => 'required|string|url',
        'payloadList.*.config.show' => 'required|boolean',
        'payloadList.*.config.title' => 'nullable|string|max:255',
        'payloadList.*.config.description' => 'nullable|string',
        'payloadList.*.config.img' => 'nullable|string|url',
        'payloadList.*.osCategorized' => 'nullable|boolean',
        'payloadList.*.Fields' => 'required|array',
        'payloadList.*.Fields.*.id' => 'required|integer',
        'payloadList.*.Fields.*.os' => 'nullable|',
        'payloadList.*.Fields.*.label' => 'required|string|max:255',
        'payloadList.*.Fields.*.field_id' => 'required|string|max:255',
        'payloadList.*.Fields.*.value' => 'nullable',
        'payloadList.*.Fields.*.input_type' => 'required|string|max:255',
        'payloadList.*.Fields.*.description' => 'nullable|string',
        'payloadList.*.Fields.*.options' => 'array',
      ]);

      $params['profileUUID'] = $request->input('profileUUID', 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC5');
      $params['profileType'] = $request->input('profileType', 'Configuration');
      $params['profileRemovalDisallowed'] = $request->input('profileRemovalDisallowed', 0);
      $params['isEncrypted'] = $request->input('profileRemovalDisallowed', 0);
      $params['profileVersion'] = $request->input('profileVersion', 1);
      $params['onSingleDevice'] = $request->input('onSingleDevice', 0);
      $params['home'] = $request->input('home', 0);
      $params['copeMaster'] = $request->input('copeMaster', 0);
      $params['enabled'] = $request->input('enabled', 1);

      $controllerResponse = app(Controllers\UemProfileController::class)->update($id, $params);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    }  catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
  Route::get('payload/{id}', function ($id) {
    try {
      Validator::make(["id" => $id], [
        'id' => 'required|numeric|max:96',
      ])->validate();
      $controllerResponse = app(Controllers\UemProfileController::class)->getPayloadById($id);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);
    } catch (ValidationException $e) {
      return response(['success' => false, 'message' => 'BAD_REQUEST'], 400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::post('payloadList', function (Request $request) {
    try {
      $osType = $request->input('osType');
      Validator::make(["osType" => $osType], [
        'osType' => 'string|max:255|in:Apple,Windows,Android,Mixed',
      ])->validate();

      /**
       * @var UemProfileController $payloadList
       */
      $controllerResponse = app(UemProfileController::class)->getPayloadList($osType);
      return response(['success' => $controllerResponse->success, 'payload' => $controllerResponse->payload], $controllerResponse->httpStatus);

    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
});


// UPDATE CHANGE DESCRIPTION, check changes on desc and name profile
