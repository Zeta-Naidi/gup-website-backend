<?php
use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;


// WAIT FOR REFACTOR
Route::group(['prefix' => 'appUsage', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {

  Route::get('/', function (Request $request) {
    try {
      //$filters = $request->query('filters');
      //$responseData = (new Controllers\AppUsageController())->list(json_decode($filters, true));
      return response(['success' => true, 'payload' => []]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
});
