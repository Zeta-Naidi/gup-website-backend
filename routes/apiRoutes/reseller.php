<?php


use App\Exceptions\CatchedExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'reseller', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  Route::get('/', function (Request $request) {
    try {
      $userAuthenticated = auth()->user()->load(['rolesUser']);
      $responseData = DB::connection($userAuthenticated->nameDatabaseConnection)
        ->table('resellers')
        ->select(['id', 'name']);
      if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        $responseData = $responseData->whereIn('id',$userAuthenticated->rolesUser->relationshipIds);
      }

      return response(['success' => true, 'payload' => $responseData->get()]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  });
  Route::get('{id}', function ($id, Request $request) {
    try {
      if (is_numeric($id) && $id > 0) {
        $responseData = DB::connection(auth()->user()->nameDatabaseConnection)
          ->table('resellers')
          ->where('id', $id)
          ->select(['id', 'name'])
          ->first();
        return response(['success' => true, 'payload' => $responseData]);
      } else
        throw new Exception("ID PARAMETER NOT VALID IN api/reseller/{id}");
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 400);
    }
  });
});
