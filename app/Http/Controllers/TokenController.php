<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TokenController extends Controller
{
  public function create($params)
  {
    try {
      $name = 'token_' . time();
      $userByModel = User::where('id',auth()->user()->id)->first();
      $token = $userByModel
        ->createToken($name, $params['abilities'] ?? ['*'], isset($params['expiresAt']) ? new \DateTime($params['expiresAt']) : null);

      return ['success' => true, 'payload' => ['token' => $token->plainTextToken]];
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  public function getUserTokens()
  {
    try {
      $tokens = DB::table('personal_access_tokens')
        ->where('tokenable_id', auth()->user()->id)
        ->where('tokenable_type', 'App\Models\User')
        ->get(['name', 'last_used_at', 'expires_at', 'abilities'])
        ->toArray();
      foreach ($tokens as $token)
        $token->abilities = json_decode($token->abilities);
      return ["success" => true , "payload" => $tokens];
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

}
