<?php

namespace App\Http\Middleware;

use App\Security\BlockedHostsHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IamPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
  public function handle(Request $request, Closure $next)
  {
    try {
      $authenticatedUser = auth()->user()->load('rolesUser');
      if($authenticatedUser->levelAdmin > 1 || $authenticatedUser->rolesUser->iamPermission)
        return $next($request);
      else{
        BlockedHostsHandler::blockUser($request->user()->username, 'NORMAL_USER_TRIED_TO_ACCESS_TO_PRIVILEGED_INFORMATION');
        BlockedHostsHandler::blockIp($request->ip(), 'NORMAL_USER_TRIED_TO_ACCESS_TO_PRIVILEGED_INFORMATION');
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
