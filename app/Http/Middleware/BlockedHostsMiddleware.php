<?php

namespace App\Http\Middleware;

use App\Security\BlockedHostsHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockedHostsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     *
     */
    public function handle(Request $request, Closure $next)
    {
      $authenticatedUser = $request->user();
      $ipRequest = $request->ip();
      $hostBlocked = isset($authenticatedUser) ?
        ($this->_checkBlockedUsers($authenticatedUser->username) || $this->_checkBlockedIps($ipRequest)) :
        $this->_checkBlockedIps($ipRequest);
      if(!$hostBlocked)
        return $next($request);
      else{
        if(isset($authenticatedUser)){
          Auth::guard('web')->logout();
          $request->session()->invalidate();
          $request->session()->regenerateToken();
        }
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
    }
    private function _checkBlockedUsers($username): bool{
      return BlockedHostsHandler::checkIfUserIsBlocked($username);
    }
    private function _checkBlockedIps($ip): bool{
      return BlockedHostsHandler::checkIfIpIsBlocked($ip);
    }
}
