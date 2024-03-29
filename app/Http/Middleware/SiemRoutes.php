<?php

namespace App\Http\Middleware;

use App\Security\BlockedHostsHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SiemRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      try {
        $userAuthenticated = $request->user()->load(['rolesUser']);
        if (strpos($userAuthenticated->nameDatabaseConnection,'_distributor_') || $userAuthenticated->rolesUser->relationship == 'distributor')
          return $next($request);
        else {
          BlockedHostsHandler::blockUser($request->user()->username, 'MDM_USER_TRIED_TO_ACCESS_TO_SIEM_INFORMATION');
          BlockedHostsHandler::blockIp($request->ip(), 'MDM_USER_TRIED_TO_ACCESS_TO_SIEM_INFORMATION');
          //Todo AccessLog
          Auth::guard('web')->logout();
          $request->session()->invalidate();
          $request->session()->regenerateToken();
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        }
      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
        return response(['message' => 'GENERIC_ERROR'], 500);
      }
    }
}
