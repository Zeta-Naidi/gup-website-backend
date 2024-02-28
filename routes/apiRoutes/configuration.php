<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\ConfigurationController;
use App\Utils\ControllerResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'configuration', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {

  Route::get('/', function (Request $request) {
    try {
      /** @var ControllerResponse $result */
      $result = app(ConfigurationController::class)->list();
      return response(['success' => $result->success, 'payload' => $result->payload], $result->httpStatus);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
  Route::post('/', function (Request $request) {
    try {
      $params = $request->validate([
        "type" => "required|in:splunk,smtp,syslog",
        "name" => "nullable|string|max:128",
        "clientIds" => "nullable|array|min:0",
        "clientIds.*" => "integer|min:1|max:1000000",
        "eventTypeIds" => "array",
        "eventTypeIds.*" => "integer|min:0|max:100",
        "splunkConfiguration" => "required_if:type,splunk|array",
        "splunkConfiguration.url" => [
          "required_if:type,splunk",
          "regex:/^(https):\/\/(?:[a-zA-Z0-9\-._~%!$&'()*+,;=]+@)?(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|[a-zA-Z0-9\-._]+)(?::\d+)?(?:\/[^\s]*)?$/"
        ],
        "splunkConfiguration.authToken" => [
          "required_if:type,splunk",
          "max:256"
        ],
        "splunkConfiguration.index" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "splunkConfiguration.source" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "splunkConfiguration.sourcetype" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "smtpConfiguration" => "required_if:type,smtp|array",
        "smtpConfiguration.protocol" => "required_if:type,smtp|in:SMTP",
        "smtpConfiguration.host" => [
          "required_if:type,smtp",
          "regex:/^([a-zA-Z0-9.-]+)$/"
        ],
        "smtpConfiguration.port" => [
          "required_if:type,smtp",
          "integer",
          "min:1",
          "max:65535"
        ],
        "smtpConfiguration.username" => "required_if:type,smtp|max:64",
        "smtpConfiguration.password" => "required_if:type,smtp|max:256",
        "smtpConfiguration.addressFrom" => "email",
        "smtpConfiguration.nameFrom" => "max:64",
        "syslogConfiguration" => "required_if:type,syslog|array",
        "syslogConfiguration.protocol" => "required_if:type,syslog|in:TCP,UDP",
        "syslogConfiguration.port" => [
          "required_if:type,syslog",
          "integer",
          "min:1",
          "max:65535"
        ],
        "syslogConfiguration.ipAddress" => [
          "required_if:type,syslog",
          "regex:/^(?:(?:[0-9]{1,3}\.){3}[0-9]{1,3})$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/",
        ],
      ]);
      $result = app(ConfigurationController::class)->create($params);
      return response(['success' => $result->success, 'payload' => $result->payload], $result->httpStatus);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });

  Route::put('/', function (Request $request){
    try {
      $params = $request->validate([
        "configurationId" => "required|min:1|max:1000000",
        "type" => "required|in:splunk,smtp,syslog",
        "name" => "nullable|string|max:128",
        "clientIds" => "nullable|array|min:0",
        "clientIds.*" => "integer|min:1|max:1000000",
        "eventTypeIds" => "array",
        "eventTypeIds.*" => "integer|min:0|max:100",
        "splunkConfiguration" => "required_if:type,splunk|array",
        "splunkConfiguration.url" => [
          "required_if:type,splunk",
          "regex:/^(https):\/\/(?:[a-zA-Z0-9\-._~%!$&'()*+,;=]+@)?(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|[a-zA-Z0-9\-._]+)(?::\d+)?(?:\/[^\s]*)?$/"
        ],
        "splunkConfiguration.authToken" => [
          "required_if:type,splunk",
          "max:256"
        ],
        "splunkConfiguration.index" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "splunkConfiguration.source" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "splunkConfiguration.sourcetype" => 'nullable|string|max:128|prohibited_if:type,syslog|prohibited_if:type,smtp',
        "smtpConfiguration" => "required_if:type,smtp|array",
        "smtpConfiguration.protocol" => "required_if:type,smtp|in:SMTP",
        "smtpConfiguration.host" => [
          "required_if:type,smtp",
          "regex:/^([a-zA-Z0-9.-]+)$/"
        ],
        "smtpConfiguration.port" => [
          "required_if:type,smtp",
          "integer",
          "min:1",
          "max:65535"
        ],
        "smtpConfiguration.username" => "required_if:type,smtp|max:64",
        "smtpConfiguration.password" => "required_if:type,smtp|max:256",
        "smtpConfiguration.addressFrom" => "email",
        "smtpConfiguration.nameFrom" => "max:64",
        "syslogConfiguration" => "required_if:type,syslog|array",
        "syslogConfiguration.protocol" => "required_if:type,syslog|in:TCP,UDP",
        "syslogConfiguration.port" => [
          "required_if:type,syslog",
          "integer",
          "min:1",
          "max:65535"
        ],
        "syslogConfiguration.ipAddress" => [
          "required_if:type,syslog",
          "regex:/^(?:(?:[0-9]{1,3}\.){3}[0-9]{1,3})$|^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/",
        ],
      ]);
      $result = app(ConfigurationController::class)->update($params);
      return response(['success' => $result->success, 'payload' => $result->payload], $result->httpStatus);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });

  Route::delete('/{id}', function ($id, Request $request){
    try {
      if (!is_int($id) && $id <= 0)
        throw new \App\Exceptions\ParametersException('ID_NOT_VALID');

      $result = app(ConfigurationController::class)->delete($id);
      return response(["success" => (bool)$result, 'payload' => $result]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
});
