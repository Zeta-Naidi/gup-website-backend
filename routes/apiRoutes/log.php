<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


Route::group(['prefix' => 'log', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  Route::get('access', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'period' => 'required|array',
        'period.from' => 'required|date_format:Y-m-d H:i:s',
        'period.to' => 'required|date_format:Y-m-d H:i:s',
        'groupBy' => 'nullable|array',
        'groupBy.*' => Rule::in(['ip', 'username', 'type']),
        'paginate' => 'nullable|boolean',
        'page' => 'required_if:paginate,true|nullable|integer|min:1',
        'rowsPerPage' => 'nullable|integer|min:10|max:150',
        'orderBy' => 'nullable|array',
        'orderBy.*.attribute' => 'required|in:createdAt,username,ip,type',
        'orderBy.*.order' => 'required|in:desc,asc',
        'type' => 'nullable|array',
        'type.*' => Rule::in([
          "SUCCESS_LOGIN",
          "LOGOUT",
          "SESSION_EXPIRED",
          "WRONG_LOGIN",
          "SUCCESS_LOGIN_MFA",
          "SUCCESS_REGISTER_MFA",
          "WRONG_LOGIN_MFA",
          "WRONG_REGISTER_MFA",
          "CREATE_USER",
          "UPDATE_USER",
          "DELETE_USER",
          "USER_BLOCKED",
          "IP_ADDRESS_BLOCKED",
          "SUCCESS_RESET_PASSWORD",
          "WRONG_RESET_PASSWORD"
        ]),
        'username' => 'nullable|array',
        'username.*' => 'nullable|string|max:92|min:4',
        'ip' => 'nullable|array',
        'ip.*' => 'ip',
        'selectAttributes' => 'required|array',
        'selectAttributes.*' => Rule::in(['ip', 'username', 'type', 'userAgent', 'toUser', 'value', 'createdAt']),
      ])->validate();
      $filters['logType'] = 0;
      $responseData = app(Controllers\LogController::class)->list($filters);
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  })->middleware('hasAccessLogPermission');
  Route::get('system', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'period' => 'required|array',
        'period.from' => 'required|date_format:Y-m-d H:i:s',
        'period.to' => 'required|date_format:Y-m-d H:i:s',
        'groupBy' => 'nullable|array',
        'groupBy.*' => Rule::in(['ip', 'username', 'type']),
        'paginate' => 'nullable|boolean',
        'page' => 'required_if:paginate,true|nullable|integer|min:1',
        'rowsPerPage' => 'nullable|integer|min:10|max:150',
        'orderBy' => 'nullable|array',
        'orderBy.*.attribute' => 'required|in:createdAt,username,ip,type',
        'orderBy.*.order' => 'required|in:desc,asc',
        'type' => 'nullable|array',
        'type.*' => Rule::in([
          "SUCCESS_LOGIN",
          "LOGOUT",
          "SESSION_EXPIRED",
          "WRONG_LOGIN",
          "SUCCESS_LOGIN_MFA",
          "SUCCESS_REGISTER_MFA",
          "WRONG_LOGIN_MFA",
          "WRONG_REGISTER_MFA",
          "CREATE_USER",
          "UPDATE_USER",
          "DELETE_USER",
          "USER_BLOCKED",
          "IP_ADDRESS_BLOCKED",
          "SUCCESS_RESET_PASSWORD",
          "WRONG_RESET_PASSWORD"
        ]),
        'username' => 'nullable|array',
        'username.*' => 'nullable|string|max:92|min:4',
        'ip' => 'nullable|array',
        'ip.*' => 'ip',
        'selectAttributes' => 'required|array',
        'selectAttributes.*' => Rule::in(['ip', 'username', 'type', 'userAgent', 'toUser', 'value', 'createdAt']),
      ])->validate();
      $filters['logType'] = 1;
      $responseData = app(Controllers\LogController::class)->list($filters);
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  })->middleware('hasSystemLogPermission');
  Route::get('administrator', function (Request $request) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, [
        'period' => 'required|array',
        'period.from' => 'required|date_format:Y-m-d H:i:s',
        'period.to' => 'required|date_format:Y-m-d H:i:s',
        'groupBy' => 'nullable|array',
        'groupBy.*' => Rule::in(['ip', 'username', 'type']),
        'paginate' => 'nullable|boolean',
        'page' => 'required_if:paginate,true|nullable|integer|min:1',
        'rowsPerPage' => 'nullable|integer|min:10|max:150',
        'orderBy' => 'nullable|array',
        'orderBy.*.attribute' => 'required|in:createdAt,username,ip,type',
        'orderBy.*.order' => 'required|in:desc,asc',
        'type' => 'nullable|array',
        'type.*' => Rule::in([
          "SUCCESS_LOGIN",
          "LOGOUT",
          "SESSION_EXPIRED",
          "WRONG_LOGIN",
          "SUCCESS_LOGIN_MFA",
          "SUCCESS_REGISTER_MFA",
          "WRONG_LOGIN_MFA",
          "WRONG_REGISTER_MFA",
          "CREATE_USER",
          "UPDATE_USER",
          "DELETE_USER",
          "USER_BLOCKED",
          "IP_ADDRESS_BLOCKED",
          "SUCCESS_RESET_PASSWORD",
          "WRONG_RESET_PASSWORD"
        ]),
        'username' => 'nullable|array',
        'username.*' => 'nullable|string|max:92|min:4',
        'ip' => 'nullable|array',
        'ip.*' => 'ip',
        'selectAttributes' => 'required|array',
        'selectAttributes.*' => Rule::in(['ip', 'username', 'type', 'userAgent', 'toUser', 'value', 'createdAt']),
      ])->validate();
      $responseData = app(Controllers\LogController::class)->listAdmin($filters);
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  })->middleware('isSuperAdmin');
});
