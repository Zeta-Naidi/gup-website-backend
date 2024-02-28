<?php

namespace App\Providers;

use App\Exceptions\CatchedExceptionHandler;
use App\Security\BlockedHostsHandler;
use App\Utils\UsefulFunctions;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * The path to the "home" route for your application.
   *
   * Typically, users are redirected here after authentication.
   *
   * @var string
   */
  public const HOME = '/home';

  /**
   * Define your route model bindings, pattern filters, and other route configuration.
   *
   * @return void
   */
  public function boot()
  {
    $this->configureRateLimiting();

    $this->routes(function () {
      Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));

      Route::middleware('web')
        ->group(base_path('routes/web.php'));
    });
  }

  /**
   * Configure the rate limiters for the application.
   *
   * @return void
   */
  protected function configureRateLimiting()
  {
    RateLimiter::for('api', function (Request $request) {

      try {
        return Limit::perMinute(600)->by($request->user()?->id ?: $request->ip())->response(function (Request $request, array $headers) {
          if (isset($request->user()->id)) {
            BlockedHostsHandler::blockUser($request->user()->username, 'REACHED_LIMIT_GENERAL_API_REQUESTS');
            BlockedHostsHandler::blockIp($request->ip(), 'REACHED_LIMIT_GENERAL_API_REQUESTS');
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
          } else
            BlockedHostsHandler::blockIp($request->ip(), 'REACHED_LIMIT_GENERAL_API_REQUESTS');
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('login', function (Request $request) {
      try {
        $requestValidated = $request->validate([
          'username' => [
            'required',
            'min:6',
            'regex:/^(?:[a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}|[a-zA-Z0-9_-]+)$/']
        ]);
        return Limit::perMinutes(10,5)->by('login_try_' . ($requestValidated["username"] ?? UsefulFunctions::clearStringFromSpecialCharacters($request->ip())))->response(function (Request $request, array $headers) use ($requestValidated) {
          BlockedHostsHandler::blockIp($request->ip(),'REACHED_LIMIT_ATTEMPTS_API_LOGIN');
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('confirmLogin', function (Request $request) {
      try {
        return Limit::perMinute(3)
          ->by('CONFIRM_LOGIN_TRY_' . UsefulFunctions::clearStringFromSpecialCharacters($request->ip()))
          ->response(function (Request $request, array $headers) {
            try {
              BlockedHostsHandler::blockIp($request->ip(),'REACHED_LIMIT_ATTEMPTS_API_CONFIRM_LOGIN');
              return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
            } catch (\Exception $e) {
              CatchedExceptionHandler::handle($e);
              return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
            }
          });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('setOtpByEmail', function (Request $request) {
      try {
        return Limit::perHour(2)->by('SET_OTP_BY_EMAIL_' . UsefulFunctions::clearStringFromSpecialCharacters($request->ip()))
          ->response(function (Request $request, array $headers) {
            BlockedHostsHandler::blockIp($request->ip(),'REACHED_LIMIT_ATTEMPTS_API_SET_OTP_BY_MAIL');
            return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
          });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('requestMfaCode', function (Request $request) {
      try {
        $user = $request->user();
        return Limit::perHour(2)->by('request_mfa_try_' . $user->username)->response(function (Request $request, array $headers) {
          BlockedHostsHandler::blockUser($request->user()->username, 'REACHED_LIMIT_MFA_REGISTER_API_REQUESTS');
          Auth::guard('web')->logout();
          $request->session()->invalidate();
          $request->session()->regenerateToken();
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('confirmMfaCode', function (Request $request) {
      try {
        $user = $request->user();
        return Limit::perMinute(3)->by('confirm_mfa_try_' . $user->username)->response(function (Request $request, array $headers) {
          BlockedHostsHandler::blockUser($request->user()->username, 'REACHED_LIMIT_ATTEMPTS_MFA_REGISTER_CONFIRM_API_REQUESTS');
          Auth::guard('web')->logout();
          $request->session()->invalidate();
          $request->session()->regenerateToken();
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('requestResetPassword', function (Request $request) {
      try {
        $requestValidated = $request->validate([
          'email' => 'required|string'
        ]);
        return Limit::perHour(2)->by('request_resetPassword_try_' . $requestValidated['email'])->response(function (Request $request, array $headers) {
          BlockedHostsHandler::blockIp($request->ip(),'REACHED_LIMIT_ATTEMPTS_API_REQUEST_RESET_PASSWORD');
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });

    RateLimiter::for('confirmResetPassword', function (Request $request) {
      try {
        $requestValidated = $request->validate([
          'email' => 'required|string'
        ]);
        return Limit::perHour(4)->by('confirm_resetPassword_try_' . $requestValidated['email'])->response(function (Request $request, array $headers) {
          BlockedHostsHandler::blockIp($request->ip(),'REACHED_LIMIT_ATTEMPTS_API_CONFIRM_RESET_PASSWORD');
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        });
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(["success" => false, "message" => "GENERIC_SERVER_ERROR"], 500);
      }
    });
  }


}
