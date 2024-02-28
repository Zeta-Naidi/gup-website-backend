<?php

namespace App\Http\Middleware;

use App\Security\BlockedHostsHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class isSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

  public function handle(Request $request, Closure $next)
  {
    try {
      //THIS MIDDLEWARE IS ALWAYS SET AFTER AUTH MIDDLEWARE, NO NEED TO CHECK IF USER IS AUTHENTICATED
      if($request->user()->levelAdmin > 1)
        return $next($request);
      else{
        BlockedHostsHandler::blockUser($request->user()->username,'ADMIN_USER_TRIED_TO_ACCESS_TO_SUPER_ADMIN_INFORMATION');
        BlockedHostsHandler::blockIp($request->ip(),'ADMIN_USER_TRIED_TO_ACCESS_TO_SUPER_ADMIN_INFORMATION');
        //Todo AccessLog
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
    }
    catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR'], 500);
    }
  }
}
