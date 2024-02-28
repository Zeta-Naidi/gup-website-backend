<?php

namespace App\Http\Middleware;

use App\Security\BlockedHostsHandler;
use Closure;
use Illuminate\Http\Request;

class SpaUserAuthenticatedMiddleware
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
      // if user is spa authenticated it return always true, otherwise none of the api tokens has spaAuthenticated permission
      if($request->user()->tokenCan('spaAuthenticated'))
        return $next($request);
      else{
        BlockedHostsHandler::blockIp($request->ip(), 'ATTEMPT_API_TOKEN_ACCESS_TO_NOT_API_ROUTE');
        BlockedHostsHandler::blockUser($request->user(), 'ATTEMPT_API_TOKEN_ACCESS_TO_NOT_API_ROUTE');
        return response([],500);
      }

    }
}
