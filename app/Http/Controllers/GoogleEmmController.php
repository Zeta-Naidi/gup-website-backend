<?php

namespace App\Http\Controllers;

use App\Services\AndroidEnterpriseService;
use Carbon\Carbon;
use Google\Exception;
use Google_Client;
use Google_Service_Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class GoogleEmmController extends Controller
{
  private Google_Client $client;
  private AndroidEnterpriseService $service;
  private string $developerKey;
  private string $authConfigPath;

  public function generateSignupUrl(Request $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
  {
    $this->client = new Google_Client();
    $this->service = new AndroidEnterpriseService($this->client);
    try {
      $validated = $request->validate(
        [
          'developerKey' => 'required|string',
          'authConfigPath' => 'required|string'
        ]
      );
      $this->developerKey = $validated['developerKey'] ?? null;
      $this->authConfigPath = strlen($validated['authConfigPath']) > 0 ? base_path() . $validated['authConfigPath'] : base_path() . "\\app\\chimpa-mdm-9a94557cc550.json";
      if (!$this->developerKey || !$this->authConfigPath) {
        return response(['success' => false, 'message' => 'Missing required parameters'], 400);
      }
      $this->client->setDeveloperKey($this->developerKey);
      $this->client->setAuthConfig($this->authConfigPath);
      $this->client->addScope(AndroidEnterpriseService::ANDROIDENTERPRISE);

      Cache::put('developerKey', $this->developerKey, now()->addMinutes(29));
      Cache::put('authConfigPath', $this->authConfigPath, now()->addMinutes(29));


      $sesId = uniqid("test");
      $signupResponse = null;
      try {
        $signupResponse = $this->service->generateSignupUrl(array("callbackUrl" => "https://localhost:8000/" . "api/panel/googleEmm?s=" . rawurlencode($sesId) . '&experimentalBte'));
        $completionToken = $signupResponse->getCompletionToken();
        Cache::put('completionToken', $completionToken, now()->addMinutes(29));
        return ['success' => true, 'payload' => $signupResponse];
      } catch (\Google_Service_Exception $exception) {
        Log::error($exception);
        $errorMessages = $exception->getMessages();
        return response(['success' => false, 'message' => 'Google_Service_Exception', 'errors' => $errorMessages], 422);
      }
    } catch (\Illuminate\Validation\ValidationException $ve) {
      $errorMessages = $ve->validator->errors()->all();
      Log::error('Validation Error: ' . implode(', ', $errorMessages));
      return response(['success' => false, 'message' => 'VALIDATION_ERROR', 'errors' => $errorMessages], 422);
    } catch (Exception $e) {
      Log::error($e);
      $errorMessages = $e->getMessages();
      return response(['success' => false, 'message' => 'Google_Service_Exception', 'errors' => $errorMessages], 500);
    }
  }

  public function signupCallback(Request $request): \Illuminate\Foundation\Application|Response|\Illuminate\Http\JsonResponse|array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
  {
    $this->client = new Google_Client();
    $this->service = new AndroidEnterpriseService($this->client);
    try {
      $validated = $request->validate(
        [
          'sessionId' => 'required|string',
          'enterpriseToken' => 'required|string'
        ]
      );
      $sessionId = $validated['sessionId'] ?? null;
      $enterpriseToken = $validated['enterpriseToken'] ?? null;
      if (!$sessionId || !$enterpriseToken) {
        return ['success' => false, 'message' => 'Missing required parameters'];
      }
    } catch (\Illuminate\Validation\ValidationException $ve) {
      $errorMessages = $ve->validator->errors()->all();
      Log::error('Validation Error: ' . implode(', ', $errorMessages));
      return ['success' => false, 'message' => 'VALIDATION_ERROR', 'errors' => $errorMessages];
    }

    $this->developerKey = Cache::get('developerKey');
    $this->authConfigPath = Cache::get('authConfigPath');
    $completionToken = Cache::get('completionToken');
    $this->client->setDeveloperKey($this->developerKey);
    $this->client->setAuthConfig($this->authConfigPath);
    $this->client->addScope(AndroidEnterpriseService::ANDROIDENTERPRISE);

    $signupRes = null;

    if (isset($completionToken) && !is_null($completionToken) && $completionToken != "") {
      try {
        $signupRes = $this->service->signupCallback($completionToken, $enterpriseToken);
      } catch (Google_Service_Exception $e) {
        Log::error($e);
        return response(
          [
            'success' => false,
            'message' => json_decode($e->getMessage())->error->status ?? json_decode($e->getMessage())->error,
            'error' => json_decode($e->getMessage())->error
          ], $e->getCode());
      } catch (Exception $e) {
        Log::error($e);
        $message = $e->getMessage();
        return response(['success' => false, 'message' => $message, 'error' => $e], 500);
      }
    }
    $recordId = Cache::get('recordId');

    $completed = DB::connection('testing_mdm_prova_d3tGk')->table('config')
      ->where('id', $recordId)
      ->update(['completion' => json_encode($signupRes)]);
    Cache::forget('recordId');

    if($completed) {
      return response()->json($signupRes);
    } else {
      return response()->json(['success' => false, 'message' => 'No matching record found to update.'], 422);
    }
  }

  public function store(Request $request): \Illuminate\Http\RedirectResponse
  {
    $session = $request->query('s');
    $enterpriseToken = $request->query('enterpriseToken');

    $frontendUrl = 'http://localhost:8080/registration-complete';
    $queryParams = http_build_query([
      's' => $session,
      'enterpriseToken' => $enterpriseToken,
    ]);

    $cookies = $this->setEmmCookies($session, $enterpriseToken);

    if(Cache::has('recordId')) {
      DB::connection('testing_mdm_prova_d3tGk')->table('config')->update([
        'type' => 'androidEnterprise',
        'value' => json_encode([
          'session' => $session,
          'enterpriseToken' => $enterpriseToken
        ]),
        'updated_at' => Carbon::now(),
      ]);
    } else {
      $recordId = DB::connection('testing_mdm_prova_d3tGk')->table('config')->insertGetId(
        [
          'type' => 'androidEnterprise',
          'value' => json_encode([
            'session' => $session,
            'enterpriseToken' => $enterpriseToken
          ]),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ],
      );
      Cache::put('recordId', $recordId, now()->addMinutes(29));
    }

    return redirect()->to($frontendUrl . '?' . $queryParams)->withCookies($cookies);
  }

  private function setEmmCookies($emm1, $emm2): array
  {
    $cookieOne = Cookie::make('emm_a', $emm1, $minutes = 60, $path = null, $domain = null, $secure = true, $httpOnly = true);
    $cookieTwo = Cookie::make('emm_b', $emm2, $minutes = 60, $path = null, $domain = null, $secure = true, $httpOnly = true);

    return [$cookieOne, $cookieTwo];
  }
}
