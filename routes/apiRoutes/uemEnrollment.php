<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

//Route::group(['prefix' => 'uem/profile', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts']], function () {
Route::group(['prefix' => 'uem/enrollment'], function () {
  Route::post('check_date_time', function (Request $request) {
    try {
      $params = $request->validate([
        'timeStamp' => 'required|integer',
        'agentVersion' => 'required|integer',
        'userAgent' => 'required|string|max:255',
        'deviceId' => 'required|integer'
      ]);

      $params['timeStamp'] = $request->input('timeStamp', 0);
      $params['agentVersion'] = $request->input('agentVersion', -1);
      $params['userAgent'] = $request->input('userAgent', 'useragent_eu.chimpa.mdmagent');
      $params['deviceId'] = $request->input('deviceId', -1);

      return response(app(Controllers\UemEnrollmentController::class)->check_date_time($params));
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"], 500);
    }
  });
  Route::get('androidEnrollment', function () {
    try {
      $enrollmentQRCode = app(Controllers\UemEnrollmentController::class)->getAndroidEnrollment();
      return response(["success" => true, "payload" => $enrollmentQRCode]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::get('standardEnrollment', function () {
    try {
      $standardEnrollment = app(Controllers\UemEnrollmentController::class)->getStandardEnrollment();
      return response(["success" => true, "payload" => $standardEnrollment]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::post('enroll', function (Request $request) {
    try {
      $params = $request->validate([
        'mdmToken' => 'required|string',
        'agentversion' => 'required|string',
        'vendorId' => 'required|string',
        'encryptedContent' => 'required|string',
      ]);

      $encryptedContent = $request->json('encryptedContent');

      $Enrollment = app(Controllers\UemEnrollmentController::class)->enrollment($params, $encryptedContent);
      return response(["success" => true, "payload" => $Enrollment]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

  Route::post('setupConfirmation', function(Request $request) {
    try {
      $params = $request->validate([
        'deviceId' => 'required|number',
        'mdmToken' => 'required|string',
        'agentversion' => 'required|string',
        'setup' => 'required|number',
      ]);

      $setupConfirmation = app(Controllers\UemEnrollmentController::class)->setupConfirmation($params);
      return response(["success" => true, "payload" => $setupConfirmation]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

  Route::get('get_logic', function () {
    try {
      $standardEnrollment = app(Controllers\UemEnrollmentController::class)->getLogic();
      return response(["success" => true, "payload" => $standardEnrollment]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });

});
