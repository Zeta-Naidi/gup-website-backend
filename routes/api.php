<?php

use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$files = glob(__DIR__ . '/apiRoutes/*.php');
foreach ($files as $file)
  require $file;

Route::get('redirectToChimpa/{clientId}', [\App\Http\Controllers\AuthController::class, 'requestAccessTokenForRedirect'])->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers']);

Route::get('test', [\App\Http\Controllers\TestController::class, 'test'])->middleware(['auth:sanctum', 'onlySpaAuthenticateUsers']);
//Route::get('test2', [\App\Http\Controllers\TestController::class, 'test2']);
//Route::get('test3', [\App\Http\Controllers\TestController::class, 'test3']);
//Route::get('test4', [\App\Http\Controllers\TestController::class, 'test4']);
//Route::get('createMDM', [\App\Http\Controllers\DatabaseConnectionController::class, 'createMDM']);
//Route::get('migrateMDM', [\App\Http\Controllers\DatabaseConnectionController::class, 'migrateMDMDatabase']);
//Route::get('test5', [\App\Http\Controllers\TestController::class, 'test5']);
//Route::get('test6', [\App\Http\Controllers\TestController::class, 'test6']);
/*Route::get('test7',  function (Request $request) {
  return app(\App\Http\Controllers\TestController::class)->test7($request);
});
Route::post('test8',  function (Request $request) {
    app(\App\Http\Controllers\TestController::class)->test8($request);
});*/

//Route::post('/panel/generateSignupUrl', [Controllers\GoogleEmmController::class, 'generateSignupUrl']);
//Route::post('/panel/signupCallback', [Controllers\GoogleEmmController::class, 'signupCallback']);
// callback endpoint for android enterprise
//Route::get('/panel/googleEmm', [\App\Http\Controllers\GoogleEmmController::class, 'store']);

Route::get('/test/windows',  function (Request $request) {
  return app(\App\Devices\WindowsDevice::class)->sendPushNotification((array)$request->input('tokens'));
});

Route::get('/test/google',  function (Request $request) {
  return app(\App\Devices\GoogleDevice::class)->sendPushNotification('android', "BHO", "MDM", false, [["1" => 123456], ["2" => 876543],], "THE MESSAGE");
});

Route::get('/test/apple',  function (Request $request) {
  return app(\App\Devices\AppleDevice::class)->sendPushNotification(1234);
});

