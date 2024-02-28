<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GaeProxyIp
{
  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    try {
      // Google App Engine includes the client's IP as the first item in
      // X-Forwarded-For, but nowhere else; REMOTE_ADDR is empty.
      if (isset($_SERVER['GAE_SERVICE'])) {
        $forwardedFor = array_map('trim', explode(',', $request->header('x-forwarded-for')));
        $request->server->set('REMOTE_ADDR', $_SERVER['REMOTE_ADDR'] = $forwardedFor[0]);
      }
      if ($request->getRequestUri() == '/handle-task') {
        if (config('app.hostType') != 'cloud_appEngine')
          throw new \Exception('Not Google Cloud Service tried to call /handle-task');
      }
      return $next($request);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }
}
