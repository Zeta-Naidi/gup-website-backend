<?php

namespace App\Http\Controllers;

use App\Jobs\LogAccess;
use App\Mail\OtpMfaByMail;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Security\BlockedHostsHandler;
use App\Security\MfaHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

class AuthController extends Controller
{
  public function login($params): Response
  {
    try {
      $request = app()->request;
      if (str_contains($params['username'], '@'))
        $userWithUsername = User::where('email', $params["username"])->with('rolesUser')->first();
      else
        $userWithUsername = User::where('username', $params["username"])->with('rolesUser')->first();
      $fakeHash = "$2y$14$1Ep7qax/aB2RKV30bXHUYucxAVDOrqCJBr/zby1cLf8ZAcO11fxbS"; //Hash of a random string containing 50 characters, to prevent timing attacks
      $check = Hash::check($params["password"], !$userWithUsername ? $fakeHash : $userWithUsername->password);
      if ($check && $userWithUsername) {
        $mfaHandler = new MfaHandler($userWithUsername->id, $request->userAgent(), $request->ip());
        if (
          ($userWithUsername->otpKey || $userWithUsername->mfaByEmail) &&
          (
            !$request->cookie('deviceId') ||
            ($mfaHandler->verifyCookieDeviceId($request->cookie('deviceId')) != 1) ||
            (empty($userWithUsername->lastMfaCheck) || $this->_dateOneWeekOld($userWithUsername->lastMfaCheck))
          )
        ) {
          //User has to Mfa authenticate
          $accessToken = Str::random(128);
          $functionForChoosingWhatToUpdate = function ($statusCheck) {
            switch ($statusCheck) {
              case 0:
              case -1:
                return "CREATE";
              case 1:
                return "UPDATE_ONLY_LAST_ACCESS";
              case 2:
                return "UPDATE_IP";
              case 3:
                return "UPDATE_USER_AGENT";
              case 4:
                return "UPDATE_USER_AGENT_AND_IP";
              default:
                return "NOTHING";
            }
          };
          $request->session()->put('userToMfaAuthenticate',
            ["userId" => $userWithUsername->id,
              "accessToken" => $accessToken,
              "operationAfterAuthentication" =>
                !$request->cookie('deviceId') ? "CREATE" :
                  $functionForChoosingWhatToUpdate($mfaHandler->verifyCookieDeviceId($request->cookie('deviceId')))
            ]);

          $mode = $userWithUsername->mfaByEmail && empty($userWithUsername->otpKey) ? 'mfaByEmail' : 'otpKey';

          return response(['success' => false, 'message' => 'MUST_MFA_LOGIN', 'mode' => $mode,'payload' => ['accessToken' => $accessToken]]);
        }
        if (
          $userWithUsername->otpKey &&
          $request->cookie('deviceId') &&
          $mfaHandler->verifyCookieDeviceId($request->cookie('deviceId')) == 1
        ) {
          //Update Last Access
          $mfaHandler->updateLastAccess($request->cookie('deviceId'));
        }
        //Login User
        return $this->_validateSession($userWithUsername, $request);
      } else {
        dispatch(new LogAccess(
          from: [
            "username" => $userWithUsername->username ?? null,
            "ip" => $request->ip(),
            "userAgent" => $request->userAgent()
          ],
          to: null,
          type: "WRONG_LOGIN",
          value: ['username' => $params["username"]], distributorId: $userWithUsername->distributor_id ?? null, timestamp: new \DateTime(),
          rolesUser: isset($userWithUsername->username) ? $userWithUsername->rolesUser : null
        ));
        return response(["success" => false, 'message' => 'INVALID_CREDENTIALS'], 400);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  public function confirmLogin($params): Response
  {
    try {
      $request = app()->request;
//---- CHECK REQUEST IS VALID --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
      $userStoredInSession = $request->session()->get('userToMfaAuthenticate');
      if (!$userStoredInSession) {
        //Malicious request
        BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_CONFIRM_LOGIN');
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
      $userDB = User::where('id', $userStoredInSession["userId"])
        ->with('rolesUser')
        ->first();
      if (!$userDB || ($params["accessToken"] != $userStoredInSession["accessToken"])) {
        //Malicious request
        BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_CONFIRM_LOGIN');
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
//---- CHECK OTP IS VALID --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
      if ($params["mod"] == 'EMAIL') {
        if (!Cache::has($userDB->id . '_MFA_BY_EMAIL')) {
          BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_CONFIRM_LOGIN'); //Malicious request
          return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
        }
//        if (Cache::get($userDB->id . '_MFA_BY_EMAIL') != $params["otp"])
//          return response(["success" => false, "message" => "OTP_NOT_CORRECT"]);
//        Cache::delete($userDB->id . '_MFA_BY_EMAIL');
        $emailOtp = $this->confirmMfaCodeEmail(emailOtp: $params["otp"], mod: 'LOGIN', userTryingToAuthenticate: $userDB);
        if (!$emailOtp)
          return response(["success" => false, "message" => "OTP_NOT_CORRECT"]);

      } else if ($params["mod"] == 'AUTHENTICATOR') {
        $otpValid = $this->confirmMfaCodeAuthenticator(otpCode: $params["otp"], secretKeyFromUser: $userDB->otpKey, mod: 'LOGIN', userTryingToAuthenticate: $userDB);
        if (!$otpValid)
          return response(["success" => false, "message" => "OTP_NOT_CORRECT"]);
      } else {
        //Malicious request
        BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_CONFIRM_LOGIN');
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
//---- IF LOGIC ARRIVES HERE THE OTP IS VALID AND THE USER HAS TO BE LOGGED IN --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
      $mfaHandler = new MfaHandler($userDB->id, $request->userAgent(), $request->ip());
      if ($userStoredInSession["operationAfterAuthentication"] == "CREATE")
        $mfaHandler->createUserDevice();
      else
        $mfaHandler->updateUserDevice($request->cookie('deviceId'), $userStoredInSession["operationAfterAuthentication"]);

      $userDB->lastMfaCheck = new \DateTime();
      $userDB->save();
      $request->session()->pull('userToMfaAuthenticate');//Remove session data

      return $this->_validateSession($userDB, $request);

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  // set otp by email, used to verify login by email
  public function setOtpByEmail($params)
  {
    try {
      $request = app()->request;
      $userStoredInSession = $request->session()->get('userToMfaAuthenticate');
      if (!$userStoredInSession) {
        BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_SET_OTP_BY_MAIL'); //Malicious request
        return response(["success" => false, "message" => "TOO_MANY_ATTEMPTS"], 429);
      }
      $userDB = User::where('id', $userStoredInSession["userId"])->first();
      if (!$userDB || ($params["accessToken"] != $userStoredInSession["accessToken"])) {
        BlockedHostsHandler::blockIp($request->ip(), 'ERROR_API_SET_OTP_BY_MAIL'); //Malicious request
        return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
      }
      $otp = mt_rand(100000, 999999);
      Mail::to($userDB->email)->send(new OtpMfaByMail($userDB->username, $otp));
      Cache::set($userDB->id . '_MFA_BY_EMAIL', $otp, 310); // 5 minutes + offset
      return response(['success' => true]);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  public function requestAccessTokenForRedirect($clientId)
  {
    try {
      $userAuthenticated = auth()->user();
      $connection = $userAuthenticated->nameDatabaseConnection;

      $client = \App\Models\Client::on($connection)->where('id', $clientId)->first();
      if (App::environment('production')) {
        return [
          "success" => true,
          "payload" => [
            "clientHost" => $client->host,
            "clientBaseUrl" => $client->baseUrl
          ]
        ];
      } else {
        return [
          "success" => true,
          "payload" => [
            "clientHost" => "testcloud.chimpa.eu",
            "clientBaseUrl" => "testandroid",
          ]
        ];
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR', 500]);
    }
  }

  // Function to register mfa by authenticator
  public function requestMfaCodeAuthenticator()
  {
    try {
      $userAuthenticated = auth()->user();
      if (isset($userAuthenticated->otpKey)) {
        throw new \Exception("Tried to force MFA by " . $userAuthenticated->username);
      }
      $secretKey = trim(Base32::encodeUpper(random_bytes(64)), '=');
      $totp = TOTP::createFromSecret($secretKey); // New TOTP with custom secret
      $totp->setLabel('Ermetix_' . $userAuthenticated->email); // The label (string)
      Cache::put($userAuthenticated->id . '_otpCode', Crypt::encryptString($secretKey), 95);
      return $totp->getProvisioningUri();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR', 500]);
    }
  }

  // Function to register mfa by email
  public function requestMfaCodeEmail()
  {
    try {
      $userAuthenticated = auth()->user();
      $otp = mt_rand(100000, 999999);
      Mail::to($userAuthenticated->email)->send(new OtpMfaByMail($userAuthenticated->username, $otp));
      Cache::set($userAuthenticated->id . '_MFA_BY_EMAIL', $otp, 310); // 5 minutes + offset
      return response(['success' => true]);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  //Confirm code by authenticator, both register and login method
  public function confirmMfaCodeAuthenticator($otpCode, $secretKeyFromUser, $mod, $userTryingToAuthenticate = null): bool
  {
    //confirmMfaCode chiamato solo dal API Route::post('confirmMfaCode'), quindi con $mod === 'REGISTER'
    try {
      if ($mod === 'REGISTER') {
        $userAuthenticated = auth()->user()->load('rolesUser');
        if (Cache::has($userAuthenticated->id . '_otpCode')) {
          $secretKey = Crypt::decryptString(Cache::get($userAuthenticated->id . '_otpCode'));
        }
      } else if ($mod === 'LOGIN') {
        if (empty($userTryingToAuthenticate))
          throw new \Exception('Trying to login MFA without user infos');
        $secretKey = Crypt::decryptString($secretKeyFromUser);
      } else
        return false;
      $totp = TOTP::createFromSecret($secretKey); // New TOTP with custom secret
      if ($totp->verify($otpCode)) {
        if ($mod === 'REGISTER') {
          $userAuthenticated->otpKey = Crypt::encryptString($secretKey);
          $userAuthenticated->save();
        }
        dispatch(new LogAccess(
          from: [
            "username" => $mod === 'REGISTER' ? app()->request->user()->username : $userTryingToAuthenticate->username,
            "ip" => app()->request->ip(),
            "userAgent" => app()->request->userAgent()
          ],
          to: null,
          type: $mod === 'REGISTER' ? "SUCCESS_REGISTER_MFA" : 'SUCCESS_LOGIN_MFA',
          value: ["otp" => $otpCode],
          distributorId: $mod === 'REGISTER' ? app()->request->user()->distributor_id : $userTryingToAuthenticate->distributor_id,
          timestamp: new \DateTime(),
          rolesUser: $mod === 'REGISTER' ?
            $userAuthenticated->rolesUser :
            $userTryingToAuthenticate->rolesUser,
        ));
        return true;
      }
      else {
        dispatch(new LogAccess(
          from: [
            "username" => $mod === 'REGISTER' ? app()->request->user()->username : $userTryingToAuthenticate->username,
            "ip" => app()->request->ip(),
            "userAgent" => app()->request->userAgent()
          ],
          to: null,
          type: $mod === 'REGISTER' ? "WRONG_REGISTER_MFA" : 'WRONG_LOGIN_MFA',
          value: ["otp" => $otpCode],
          distributorId: $mod === 'REGISTER' ?  app()->request->user()->distributor_id : $userTryingToAuthenticate->distributor_id,
          timestamp: new \DateTime(),
          rolesUser: $mod === 'REGISTER' ?
            $userAuthenticated->rolesUser :
            $userTryingToAuthenticate->rolesUser
        ));
        return false;
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  //Confirm code by email, both register and login method
  public function confirmMfaCodeEmail($emailOtp, $mod, $userTryingToAuthenticate = null): bool
  {
    try {
      if ($mod === 'REGISTER') {

        $userAuthenticated = auth()->user()->load('rolesUser');

        if (!Cache::has($userAuthenticated->id . '_MFA_BY_EMAIL')) {
          throw new \Exception('MFA error');
        }

        if(Cache::get($userAuthenticated->id . '_MFA_BY_EMAIL') != $emailOtp){
          return false;
        }

        $userAuthenticated->mfaByEmail = true;
        $userAuthenticated->save();

        Cache::delete($userAuthenticated->id . '_MFA_BY_EMAIL');

        dispatch(new LogAccess(
          from: [
            "username" => app()->request->user()->username,
            "ip" => app()->request->ip(),
            "userAgent" => app()->request->userAgent()
          ],
          to: null,
          type: "SUCCESS_REGISTER_MFA",
          value: ["otp" => $emailOtp],
          distributorId: app()->request->user()->distributor_id,
          timestamp: new \DateTime(),
          rolesUser: $userAuthenticated->rolesUser,
        ));
        return true;

      }
      else if ($mod === 'LOGIN') {
        if (empty($userTryingToAuthenticate)) {
          throw new \Exception('Trying to login MFA without user infos');
        }

        if (!Cache::has($userTryingToAuthenticate->id . '_MFA_BY_EMAIL') && Cache::get($userTryingToAuthenticate->id . '_MFA_BY_EMAIL') !== $emailOtp) {
          dispatch(new LogAccess(
            from: [
              "username" => $userTryingToAuthenticate->username,
              "ip" => app()->request->ip(),
              "userAgent" => app()->request->userAgent()
            ],
            to: null,
            type: 'WRONG_LOGIN_MFA',
            value: ["otp" => $emailOtp],
            distributorId: $userTryingToAuthenticate->distributor_id,
            timestamp: new \DateTime(),
            rolesUser: $userTryingToAuthenticate->rolesUser
          ));
          return false;
        }
        Cache::delete($userTryingToAuthenticate->id . '_MFA_BY_EMAIL');

        dispatch(new LogAccess(
          from: [
            "username" => $userTryingToAuthenticate->username,
            "ip" => app()->request->ip(),
            "userAgent" => app()->request->userAgent()
          ],
          to: null,
          type: 'SUCCESS_LOGIN_MFA',
          value: ["otp" => $emailOtp],
          distributorId: $userTryingToAuthenticate->distributor_id,
          timestamp: new \DateTime(),
          rolesUser: $userTryingToAuthenticate->rolesUser,
        ));
        return true;

      } else
        return false;

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }


  public function requestTokenResetPassword(Request $request): Response
  {
    try {
      $requestValidated = $request->validate(['email' => 'required|email']);
      if (!isset($requestValidated['email']))
        return response(['message' => 'Email Not Valid'], 400);

      $token = Str::random(128);
      $email = $requestValidated['email'];
      $userWithEmailChosen = User::where('email', $email)->first();
      $otp = mt_rand(100000, 999999);//TODO see better library to generate OTP

      if (!$userWithEmailChosen) //need to fake correct email
        return response(["success" => true, 'accessToken' => $token]);
      else {
        Cache::put($email . '_emailForResetPassword', ["accessToken" => $token, "email" => $email, "otp" => $otp], 300);
        Mail::to($email)->send(new ResetPasswordMail($userWithEmailChosen->username, $otp));
        return response(['success' => true, 'accessToken' => $token]);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  public function confirmResetPassword(Request $request): Response
  {
    try {
      $requestValidated = $request->validate([
        'password' => 'required|max:64|min:10',
        'email' => 'required|email',
        'accessToken' => 'required|string|min:128|max:128',
        'otp' => 'required|string|min:6|max:6'
      ]);
      if (Cache::has($requestValidated['email'] . '_emailForResetPassword')) {
        $dataToCheck = Cache::get($requestValidated['email'] . '_emailForResetPassword');
        if (($dataToCheck['accessToken'] == $requestValidated['accessToken']) && ($dataToCheck['otp'] == $requestValidated['otp'])) {
          $userToChangePassword = User::where('email', $dataToCheck['email'])
            ->with('rolesUser')
            ->first();
          $userToChangePassword->password = Hash::make($requestValidated['password'], ["rounds" => 14]);
          $userToChangePassword->save();
          Cache::forget($requestValidated['email'] . '_emailForResetPassword');
          dispatch(new LogAccess(
            from: [
              "username" => $userToChangePassword->username,
              "ip" => app()->request->ip(),
              "userAgent" => app()->request->userAgent()
            ],
            to: null, type: "SUCCESS_RESET_PASSWORD", value: ['email' => $requestValidated['email']], distributorId: $userToChangePassword->distributor_id, timestamp: new \DateTime(),
            rolesUser: $userToChangePassword->rolesUser
          ));
          return response(["success" => true, 'message' => 'PASSWORD_CHANGED']);
        } else {
          dispatch(new LogAccess(
            from: [
              "username" => null,
              "ip" => app()->request->ip(),
              "userAgent" => app()->request->userAgent()
            ],
            to: null, type: "WRONG_RESET_PASSWORD", value: ['email' => $requestValidated['email']], distributorId: null, timestamp: new \DateTime()
          ));
          return response(['success' => false, 'message' => 'DATA_NOT_CORRECT'], 400);
        }
      } else {
        return response(['success' => false, 'message' => 'DATA_NOT_CORRECT'], 400);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  }

  private function _dateOneWeekOld(string $timestamp)
  {
    $now = new \DateTime();
    $target = new \DateTime($timestamp);
    $interval = $now->diff($target);
    $days = (int)$interval->format('%a');
    return $days >= 7;
  }

  private function _validateSession($user, $request)
  {
    try {
      Auth::login($user);
      $userAuthenticated = auth()->user()->load(['rolesUser', 'databaseConnection']);
      dispatch(new LogAccess(
        from: [
          "username" => $user->username,
          "ip" => $request->ip(),
          "userAgent" => $request->userAgent()
        ],
        to: null, type: "SUCCESS_LOGIN", value: null, distributorId: $user->distributor_id, timestamp: new \DateTime(),
        rolesUser: $userAuthenticated->rolesUser
      ));
      return response(["success" => true, "payload" => [
        'id' => $userAuthenticated->id,
        'email' => $userAuthenticated->email,
        'username' => $userAuthenticated->username,
        'companyName' => $userAuthenticated->companyName,
        'isAdmin' => $userAuthenticated->levelAdmin > 1,
        'isMfaActive' => isset($userAuthenticated->otpKey) || $userAuthenticated->mfaByEmail,
        'type' => strpos($userAuthenticated->nameDatabaseConnection,'_mdm_') ? 'MDM' : 'SIEM',
        'rolesUser' => isset($userAuthenticated->rolesUser) ? [
          'relationship' => $userAuthenticated->rolesUser->relationship,
          'clientsFilter' => $userAuthenticated->rolesUser->clientsFilter,
          'scoreFilter' => $userAuthenticated->rolesUser->scoreFilter,
          'modFilter' => $userAuthenticated->rolesUser->modFilter,
          'eventTypeFilter' => $userAuthenticated->rolesUser->eventTypeFilter,
          'configurationPermission' => $userAuthenticated->rolesUser->configurationPermission,
          'iamPermission' => $userAuthenticated->rolesUser->iamPermission,
          'accessLogsPermission' => $userAuthenticated->rolesUser->accessLogsPermission,
          'systemLogsPermission' => $userAuthenticated->rolesUser->systemLogsPermission,
        ] : null,
        'distributorName' => $userAuthenticated->database_connection->distributorName ?? 'NOT_FOUND'
      ]]);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

  public function logout($userInfos): Response
  {
    try {
      $request = app()->request;
      $userAuthenticated = auth()->user()->load('rolesUser');
      Auth::guard('web')->logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      dispatch(new LogAccess(
        from: $userInfos,
        to: null, type: "LOGOUT", value: null, distributorId: $userAuthenticated->distributor_id,
        timestamp: new \DateTime(),
        rolesUser: $userAuthenticated->rolesUser
      ));
      return response(["success" => true], 204);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  }

/*  public function redirectToProvider($provider)
  {
    try{
      return Socialite::driver($provider)->redirect();
    }
    catch (\Exception $e){
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);}
  }

  public function handleProviderCallback($provider)
  {
    $user = Socialite::driver($provider)->user();

    $newUser = User::firstOrCreate([
      'email' => $user->getEmail()
    ], [
      'email_verified_at' => now(),
      'name' => $user->getName(),
      'status' => true,
      'provider' => $provider,
      'provider_id' => $user->getId(),
    ]);

    Auth::login($newUser);

    return redirect(env('SPA_URL') . '/registration-followup');
  }*/
  /**
   * Generate the name of the new connection
   * @param $distributor_id
   * @return string name of the new connection
   */
  private function _generateDatabaseName($distributor_id): string
  {
    return 'distributor_' . $distributor_id . '_d3tGk';
  }

  /*public function oldlogin(Request $request)
  { KEEP COMMENT
    try {
      $bodyRequest = $request->all();
      $code = $bodyRequest['code'];
      /**
       * Call to api to get access_token
       * @return array $response
       * $response = [
       *     "access_token" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841"
       *     "expires_in" => 3600
       *     "token_type" => "bearer"
       *     "scope" => "data.read"
       *     "refresh_token" => "bbe3fc84dd09b3832b79c6231539a4d8ddc0bfe9"
       * ]

      $response = Http::asForm()->post('https://portal.chimpa.eu/services/api/v20/mssp_token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => config('app.client_id'),
        'client_secret' => config('app.client_secret'),
        'scope' => 'data.read',
      ]);

      if ($response->status() == 200) {
        $bodyResponse = $response->json();
        /**
         * Call to api to get user information
         * @return array $user
         * $response = [
         *     "distributor_id" => "1"
         *     "companyName" => "Xnoova piccola"
         *     "username" => "example@example.com"
         *     "piva" => "IT3249723948"
         * ]

        $user = Http::withToken($bodyResponse['access_token'])->post('https://portal.chimpa.eu/services/api/v20/mssp_user_data');
        if ($user->status() == 200) {
          if ($this->_checkPresenceDistributor($user->json())) {
            $user = User::where('distributor_id', $user->json()['distributor_id'])
              ->where('username', $user->json()['username'])
              ->first();
            Auth::login($user);
            return response($user->makeHidden(['piva', 'distributor_id', 'nameDatabaseConnection'])->toArray(), 200)
              ->header('Access-Control-Allow-Origin', '*');
          } else {
            $this->_addUser($user->json());
            $user = User::where('distributor_id', $user->json()['distributor_id'])
              ->where('username', $user->json()['username'])
              ->first();
            Auth::login($user);
            \Artisan::call('queue:restart');
            return response([...$user->makeHidden(['piva', 'distributor_id', 'nameDatabaseConnection'])->toArray(), "haveToMigrate" => true], 200)
              ->header('Access-Control-Allow-Origin', '*');
          }
        } else {
          return response(['message' => 'Server Cloud not giving user authenticated infos',
          ], 500);
        }
      } else {
        return response(['message' => 'Server Cloud Not Responding'], 500);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR'], 500);
    }
  }


  /**
   * @param $distributor array of distributor credentials
   * @return bool true if the user is found in DB, false otherwise

  private function _checkPresenceDistributor($distributor): bool
  {
    $users = User::on(config('database.default'))
      ->where('distributor_id', $distributor['distributor_id'])
      ->where('username', $distributor['username'])
      ->first();
    return !is_null($users);
  }

  /**
   * @param $distributor array of distributor credentials
   * @return Model the user just created

  private function _addUser($distributor): Model
  {
    $nameDatabaseConnection = $this->_generateDatabaseName($distributor['distributor_id']);
    $user = User::on(config('database.default'))->create([
      "distributor_id" => $distributor['distributor_id'],
      "piva" => $distributor['piva'],
      "username" => $distributor['username'],
      "companyName" => $distributor['companyName'],
      "nameDatabaseConnection" => $nameDatabaseConnection,
    ]);
    //Instead of new Class(), using app(Class::class) to be able to test,
    //this method bind to the service container the class if not present and return it
    $databaseController = app(DatabaseController::class);
    $databaseController->create($nameDatabaseConnection);
    return $user;
  }
  */
}
