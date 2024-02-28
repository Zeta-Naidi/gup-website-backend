<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CronGoogleCloud
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
      if ($request->header(config('app.headerGoogleCronSchedulerKey')) == config('app.headerGoogleCronSchedulerValue'))
        return $next($request);
      else
        throw new \Exception('Scheduler Incorrectly called');
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }
}
