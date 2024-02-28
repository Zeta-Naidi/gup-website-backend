<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\UserController;
use App\Rules\AdminMustBeLinkedToDistributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'user'], function () {
  Route::post('login', function (Request $request) {
    try {
      $params = $request->validate([
        'username' => [
          'required',
          'min:6',
          'regex:/^(?:[a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|[a-zA-Z0-9_-]+)$/'
        ],
        'password' => 'required|min:10|max:65|regex:/^([a-f0-9]{64})$/',
      ]);
      return app(Controllers\AuthController::class)->login($params);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['blockedHosts', 'throttle:login']);
  Route::post('confirmLogin', function (Request $request) {
    try {
      $params = $request->validate([
        'otp' => 'required|string|min:6|max:6',
        'accessToken' => 'required|string|min:128|max:128',
        'mod' => 'required|string|in:EMAIL,AUTHENTICATOR'
      ]);
      return app(Controllers\AuthController::class)->confirmLogin($params);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['blockedHosts', 'throttle:confirmLogin']);
  Route::post('setOtpByEmail', function (Request $request) {
    try {
      $params = $request->validate([
        'accessToken' => 'required|string|min:128|max:128'
      ]);
      return app(Controllers\AuthController::class)->setOtpByEmail($params);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['blockedHosts', 'throttle:setOtpByEmail']);
  Route::post('logout', function (Request $request) {
    try {
      //Params are valid because they are get after auth middleware
      $userInfos = [
        "username" => $request->user()->username,
        "ip" => $request->ip(),
        "userAgent" => $request->userAgent(),
      ];
      return app(Controllers\AuthController::class)->logout($userInfos);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts']);
  Route::post('delete/{id}', function ($id, Request $request) {
    try {
      Validator::make(['id' => $id], [
        'id' => 'required|integer|min:1',
      ])->validate(); // it throws an exception if it's not correct
      return app(Controllers\UserController::class)->delete($id);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'hasIamPermission', 'blockedHosts']);
  Route::post('create', function (Request $request) {
    try {
      $params = $request->validate([
        'username' => 'required|min:6|max:32|alpha_dash:ascii',
        'password' => 'required|min:10|max:65|regex:/^([a-f0-9]{64})$/',
        'email' => 'required|email',
        'userRelationship' => 'required|in:distributor,reseller,client',
        'userRelationshipIds' => 'required_unless:userRelationship,distributor|array',
        'userRelationshipIds.*' => 'integer|min:0|max:100000',
        'configurationPermission' => 'required|boolean',
        'iamPermission' => 'required|boolean',
        'accessLogsPermission' => 'required|boolean',
        'systemLogsPermission' => 'required|boolean',
        'clientsFilter' => 'nullable|array',
        'clientsFilter.*' => 'integer|min:1|max:900000',//Ids of clients
        'scoreFilter' => 'nullable|array',
        'scoreFilter.*' => \Illuminate\Validation\Rule::in(['critic', 'high', 'medium', 'low']),
        'modFilter' => 'nullable|string|in:operations,monitoring',
        'eventTypeFilter' => 'nullable|array',
        'eventTypeFilter.*' => 'integer|min:0|max:100',// value of eventType
      ]);
      return response(app(UserController::class)->create($params));
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'hasIamPermission', 'blockedHosts']);
  Route::post('update', function (Request $request) {
    try {
      $params = $request->validate([
        'id' => 'required|integer|min:1',
        'username' => 'required|min:6|max:32|alpha_dash:ascii',
        'email' => 'required|email',
        'userRelationship' => 'required|in:distributor,reseller,client',
        'userRelationshipIds' => 'required_unless:userRelationship,distributor|nullable|array',
        'userRelationshipIds.*' => 'integer|min:0|max:100000',
        'configurationPermission' => 'required|boolean',
        'iamPermission' => 'required|boolean',
        'accessLogsPermission' => 'required|boolean',
        'systemLogsPermission' => 'required|boolean',
        'clientsFilter' => 'nullable|array',
        'clientsFilter.*' => 'integer|min:1|max:900000',//Ids of clients
        'scoreFilter' => 'nullable|array',
        'scoreFilter.*' => \Illuminate\Validation\Rule::in(['critic', 'high', 'medium', 'low']),
        'modFilter' => 'nullable|string|in:operations,monitoring',
        'eventTypeFilter' => 'nullable|array',
        'eventTypeFilter.*' => 'integer|min:0|max:100',// value of eventType
      ]);
      app(UserController::class)->updateWithUserRoles($params);
      return response(["success" => true]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'hasIamPermission', 'blockedHosts']);

  // PAGINATION
  Route::get('/', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $paramsFiltered = Validator::make($filters, [
        "paginate" => "nullable|boolean",
        "page" => "required_with:rowsPerPage|integer|gte:1",
        "rowsPerPage" => "required_with:paginate|integer|gte:1|max:300",
        "orderBy" => "array",
        "orderBy.*.attribute" => "required_with:orderBy.order|string|in:username,email,relationship,created_at",
        "orderBy.*.order" => "required_with:orderBy.attribute|string|in:asc,ASC,desc,DESC",
      ])->validate();
      return app(UserController::class)->list($paramsFiltered);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'hasIamPermission', 'blockedHosts']);

  //KEEP COMMENT Route::post('firstMigrate', [Controllers\DatabaseController::class, 'firstMigrate'])->middleware(['auth:sanctum', 'blockedHosts']);
  Route::get('details', function () {
    $userAuthenticated = auth()->user()->load(['rolesUser', 'databaseConnection', 'tokensAssociated']);
    return response([
      "success" => true,
      "payload" => [
        'id' => $userAuthenticated->id,
        'email' => $userAuthenticated->email,
        'username' => $userAuthenticated->username,
        'companyName' => $userAuthenticated->companyName,
        'isAdmin' => $userAuthenticated->levelAdmin > 1,
        'isMfaActive' => isset($userAuthenticated->otpKey) || $userAuthenticated->mfaByEmail,
        'type' => strpos($userAuthenticated->nameDatabaseConnection,'_mdm_') ? 'MDM' : 'SIEM',
        'rolesUser' => isset($userAuthenticated->rolesUser) ? [
          'relationship' => $userAuthenticated->rolesUser->relationship,
          'clientsFilter' => $userAuthenticated->rolesUser->clientsFilter,
          'scoreFilter' => $userAuthenticated->rolesUser->scoreFilter,
          'modFilter' => $userAuthenticated->rolesUser->modFilter,
          'eventTypeFilter' => $userAuthenticated->rolesUser->eventTypeFilter,
          'configurationPermission' => $userAuthenticated->rolesUser->configurationPermission,
          'iamPermission' => $userAuthenticated->rolesUser->iamPermission,
          'accessLogsPermission' => $userAuthenticated->rolesUser->accessLogsPermission,
          'systemLogsPermission' => $userAuthenticated->rolesUser->systemLogsPermission,
        ] : null,
        'distributorName' => $userAuthenticated->database_connection->distributorName ?? 'NOT_FOUND',
        'tokens' => isset($userAuthenticated->tokens_associated) ?
          array_map(function ($tokenRow) {
            return [
              'name' => $tokenRow['name'],
              'abilities' => $tokenRow['abilities'],
              'lastUsedAt' => $tokenRow['last_used_at'],
              'expiresAt' => $tokenRow['expires_at'],
            ];
          }, $userAuthenticated->tokens_associated) : null
      ]
    ]);
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts']);
  Route::get('requestMfaCodeAuthenticator', [Controllers\AuthController::class, 'requestMfaCodeAuthenticator'])->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts', 'throttle:requestMfaCode']);
  Route::get('requestMfaCodeEmail', [Controllers\AuthController::class, 'requestMfaCodeEmail'])->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts', 'throttle:requestMfaCode']);
  Route::post('confirmMfaCode', function (Request $request) {
    try {
      $params = $request->validate([
        'otp' => 'required|string|min:6|max:6',
        'type' => 'required|string|in:appCode,emailCode'
      ]);

      if($params['type'] === 'emailCode'){
        $result =
          app(Controllers\AuthController::class)
            ->confirmMfaCodeEmail(emailOtp: $params["otp"], mod: 'REGISTER');
      return response(["success" => $result]);
      }else{
        $result =
          app(Controllers\AuthController::class)
            ->confirmMfaCodeAuthenticator(otpCode: $params["otp"], mod: 'REGISTER', secretKeyFromUser: null);
        return response(["success" => $result]);
      }
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  })->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts', 'throttle:confirmMfaCode']);
  Route::post('requestToken/resetPassword', [Controllers\AuthController::class, 'requestTokenResetPassword'])->middleware(['blockedHosts', 'throttle:requestResetPassword']);
  Route::post('confirmNewPassword', [Controllers\AuthController::class, 'confirmResetPassword'])->middleware(['blockedHosts', 'throttle:confirmResetPassword']);
});
