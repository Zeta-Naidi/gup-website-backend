<?php

namespace App\Http\Controllers;

use App\Jobs\FetchIconApp;
use App\Models\Client;
use App\Models\Event;
use App\Security\SecurityPostureDeviceHandler;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeviceController extends CrudController
{
  public function __construct()
  {
    $this->_setDbConnectionFromAuthUser();
    $this->setModel('App\Models\Device');
  }

  public function list($filters = [])
  {
    $authenticatedUser = auth()->user();
    $query = $this->_getModel()::on($this->_getDbConnection());
    //FILTER CASES
    /*  FOR NOW NOT USED
     *   if (isset($filters["clientIds"])) {
          $query = $query->whereIn("devices.clientId", $filters["clientIds"]);
        }
        if (isset($filters["selectedAttributes"]))
          $query = $query->select(DB::raw(join(',', $filters["selectedAttributes"])));*/

    if (isset($filters["serialOrName"])) {
      $query = $query
        ->where(function ($q) use ($filters) {
          $q->where('serialNumber', 'LIKE', '%' . $filters["serialOrName"] . '%')
            ->orWhere('name', 'LIKE', '%' . $filters["serialOrName"] . '%');
        });
    }
    //RELATIONSHIP_FILTER
    if ($authenticatedUser->rolesUser->relationship == 'reseller') {
      $clientList = DB::connection($this->_getDbConnection())->table('clients')
        ->whereIn('resellerId', $authenticatedUser->rolesUser->relationshipIds)
        ->pluck('id')
        ->toArray();
      $query = $query->whereIn('clientId', $clientList);
      //Fetch list clients
    } else if ($authenticatedUser->rolesUser->relationship == 'client') {
      $query = $query->whereIn('clientId', $authenticatedUser->rolesUser->relationshipIds);
    }
    //CLIENTS_FILTER
    if (!empty($authenticatedUser->rolesUser->clientsFilter)) {
      $query = $query->whereIn('clientId', $authenticatedUser->rolesUser->clientsFilter);
    }
    /*
        NOT USED FOR NOW
         if (isset($filters["orderBy"])) {
          foreach ($filters["orderBy"] as $orderByFilter) {
            $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
          }
        }*/

    if (isset($filters["selectAttributes"]))
      $query = $query->select(...$filters["selectAttributes"]);

    return $query->get();
  }

  public function getSecurityPosture($serialNumber)
  {
    try {
      //$promise = $this->_getInstalledAppsAsync($serialNumber);
      $eventsToConsider = DB::connection($this->_getDbConnection())->table('events')
        /*        ->selectRaw(' CASE
                  WHEN detectionDate > date_sub(NOW(), INTERVAL 7 DAY) THEN "firstWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 7 DAY) and detectionDate > date_sub(NOW(), interval 14 day) THEN "secondWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 14 DAY) and detectionDate > date_sub(NOW(), interval 21 day) THEN "thirdWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 21 DAY) and detectionDate > date_sub(NOW(), interval 28 day) THEN "fourthWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 28 DAY) and detectionDate > date_sub(NOW(), interval 35 day) THEN "fifthWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 35 DAY) and detectionDate > date_sub(NOW(), interval 42 day) THEN "sixthWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 42 DAY) and detectionDate > date_sub(NOW(), interval 49 day) THEN "seventhWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 49 DAY) and detectionDate > date_sub(NOW(), interval 56 day) THEN "eighthWeek"
                  WHEN detectionDate < date_sub(NOW(), INTERVAL 56 DAY) and detectionDate > date_sub(NOW(), interval 63 day) THEN "lastWeek"
                  ELSE "doesntCount"
                END AS week,
                criticalityLevel,
                COUNT(*) AS count')*/
        ->selectRaw('event_types.key, count(*) as num, max(criticalityLevel) as maxCriticality')
        ->where('deviceSerialNumber', $serialNumber)
        ->where('hasBeenSolved', 0)
        //->where('remediationActionStarted', 0) maybe better to consider events still not repaired
        ->whereRaw('detectionDate >= DATE_SUB(NOW(), INTERVAL 60 DAY)')
        ->join('event_types', 'event_types.value', '=', 'events.type')
        ->groupBy('event_types.key')
        ->get();
      $hardeningActionsEvents = DB::connection($this->_getDbConnection())->table('events')
        ->selectRaw('events.remediationAction, events.description, events.id, events.subject')
        ->where('deviceSerialNumber', $serialNumber)
        ->where('hasBeenSolved', 0)
        ->where('remediationActionStarted', 0)
        ->whereNot('type', 1)
        ->whereNotNull('events.remediationAction')
        ->whereNot('events.remediationAction', '')
        ->whereRaw('detectionDate > DATE_SUB(NOW(), INTERVAL 60 DAY)')
        ->get();
      /*$hardeningActionsCve = DB::connection($this->_getDbConnection())->table('events')
        ->selectRaw('events.remediationAction, events.description,  events.id, events.subject')
        ->where('deviceSerialNumber', $serialNumber)
        ->where('hasBeenSolved', 0)
        ->where('remediationActionStarted', 0)
        ->where('type',1)
        ->whereNotNull('events.remediationAction')
        ->whereRaw('detectionDate > DATE_SUB(NOW(), INTERVAL 60 DAY)')
        ->get();*/
      $eventsCriticalityCounterNotResolved = DB::connection($this->_getDbConnection())->table('events')
        ->selectRaw('criticalityLevel, count(*) as num')
        ->where('deviceSerialNumber', $serialNumber)
        ->where('hasBeenSolved', 0)
        //->where('remediationActionStarted', 0)
        //->whereNotNull('events.remediationAction')
        ->whereNot('type', 1)
        ->whereRaw('detectionDate > DATE_SUB(NOW(), INTERVAL 60 DAY)')
        ->groupBy('criticalityLevel')
        ->get();
      $cveCriticalityCounterNotResolved = DB::connection($this->_getDbConnection())->table('events')
        ->selectRaw('criticalityLevel, count(*) as num')
        ->where('deviceSerialNumber', $serialNumber)
        ->where('hasBeenSolved', 0)
        //->where('remediationActionStarted', 0)
        //->whereNotNull('events.remediationAction')
        ->where('type', 1)
        ->whereRaw('detectionDate > DATE_SUB(NOW(), INTERVAL 60 DAY)')
        ->groupBy('criticalityLevel')
        ->get();
      /*if($promise instanceof PromiseInterface)
        $apps = $promise->wait();
      else
        $apps = [];*/
      $events = [];
      foreach ($eventsToConsider as $event)
        $events [$event->key] = $event;
      //https://www.php.net/manual/en/functions.first_class_callable_syntax.php
      $securityScore = app(SecurityPostureDeviceHandler::class)->calcPostureDevice2($events, $this->getInstalledApps(...), $serialNumber);
      return [
        "score" => $securityScore['score'],
        "eventCountCriticality" => $eventsCriticalityCounterNotResolved,
        "cveCountCriticality" => $cveCriticalityCounterNotResolved,
        "hardeningActions" => $hardeningActionsEvents
      ];

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(["success" => false, 'message' => 'SERVER_ERROR'], 500);
    }

  }

  public function getInstalledApps($serialNumber, $filters = ['ignoreAppServices' => true])
  {
    try {
      $device = $this->_getModel()::on($this->_getDbConnection())->where('serialNumber', $serialNumber)->first();
      if (empty($device)) {
        return [];
      }
      //handle no device
      $client = Client::on($this->_getDbConnection())->where('id', $device->clientId)->first();
      $accessToken = Http::asForm()
        ->post('https://' . $client->host . '/' . $client->baseUrl . '/api/latest/mssp/authorize', [
            'grant_type' => 'client_credentials',
            'client_id' => config('app.client_id'),
            'client_secret' => config('app.client_secret'),
            'scope' => 'data.read',
          ]
        );
      $apps = Http::withToken($accessToken['access_token'])
        ->post('https://' . $client->host . '/' . $client->baseUrl . '/api/latest/mssp/installed_apps', [
            "serial" => $serialNumber,
            "ignoreAppServices" => $filters['ignoreAppServices'] ?? null
          ]
        );
      if (($apps instanceof Response) && $apps->ok()) {
        $apps = $apps->json();
        if ($device->osType == 'android') {
          $appsWithIconToFilter = array_filter($apps, fn($el) => !empty($el['Identifier']) && empty($el["isService"]));
          $appsWithoutIcon = array_filter($apps, fn($el) => !empty($el['Identifier']) && !empty($el["isService"]));
          $appsWithIcon = [];

          foreach ($appsWithIconToFilter as $item)
            $appsWithIcon[$item['Identifier']] = $item;

          $appsInDatabase = DB::table('appsIcons')->select('iconBase64', 'identifier')
            ->whereIn('identifier', array_map(fn($el) => $el['Identifier'], $appsWithIcon))
            ->where('osType', 'android')
            ->get();
          foreach ($appsInDatabase as $appInDb) {
            $appsWithIcon[$appInDb->identifier]['icon'] = $appInDb->iconBase64 ?? null;
            $appsWithIcon[$appInDb->identifier]['alreadyChecked'] = true;
          }
          foreach (array_filter($appsWithIcon, fn($el) => empty($el['alreadyChecked'])) as $iconToFetch)
            FetchIconApp::dispatch($iconToFetch['Identifier'], 'ANDROID');

          $appsSerialized = [];
          foreach ($appsWithIcon as $elemToInsert) {
            $elemToInsert['permissionsScore'] = app(SecurityPostureDeviceHandler::class)->calcAppPermissionScore($elemToInsert['runtimePermissions'] ?? []);
            $appsSerialized [] = $elemToInsert;
          }
          foreach ($appsWithoutIcon as $elemToInsert)
            $appsSerialized [] = $elemToInsert;

          return $appsSerialized;
        } else if ($device->osType == 'ios') {
          $appsWithIcon = [];
          foreach ($apps as $item)
            $appsWithIcon[$item['Identifier']] = $item;

          $appsInDatabase = DB::table('appsIcons')->select('iconBase64', 'identifier')
            ->whereIn('identifier', array_map(fn($el) => $el['Identifier'], $appsWithIcon))
            ->where('osType', 'ios')
            ->get();
          foreach ($appsInDatabase as $appInDb) {
            $appsWithIcon[$appInDb->identifier]['icon'] = $appInDb->iconBase64 ?? null;
            $appsWithIcon[$appInDb->identifier]['alreadyChecked'] = true;
          }
          foreach (array_filter($appsWithIcon, fn($el) => empty($el['alreadyChecked'])) as $iconToFetch)
            FetchIconApp::dispatch($iconToFetch['Identifier'], 'IOS');

          $appsSerialized = [];
          foreach ($appsWithIcon as $elemToInsert)
            $appsSerialized [] = $elemToInsert;

          return $appsSerialized;
        } else return [];
      } else return [];

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return [];
    }
  }

  private function _getInstalledAppsAsync($serialNumber, $filters = ['ignoreAppServices' => true])
  {

    try {
      $device = $this->_getModel()::on($this->_getDbConnection())->where('serialNumber', $serialNumber)->first();
      if (empty($device)) {
        return [];
      }
      //handle no device
      $client = Client::on($this->_getDbConnection())->where('id', $device->clientId)->first();
      $accessToken = Http::asForm()
        ->post('https://' . $client->host . '/' . $client->baseUrl . '/api/latest/mssp/authorize', [
            'grant_type' => 'client_credentials',
            'client_id' => config('app.client_id'),
            'client_secret' => config('app.client_secret'),
            'scope' => 'data.read',
          ]
        );
      if (!($accessToken instanceof Response) || !$accessToken->ok()) {
        return [];
      }
      $promise = Http::async()
        ->withToken($accessToken['access_token'])
        ->post('https://' . $client->host . '/' . $client->baseUrl . '/api/latest/mssp/installed_apps', [
            "serial" => $serialNumber,
            "ignoreAppServices" => $filters['ignoreAppServices'] ?? null
          ]
        )->then(function ($apps) use ($device) {
          if (($apps instanceof Response) && $apps->ok()) {
            $apps = $apps->json();
            if ($device->osType == 'android') {
              $appsWithIconToFilter = array_filter($apps, fn($el) => !empty($el['Identifier']) && empty($el["isService"]));
              $appsWithoutIcon = array_filter($apps, fn($el) => !empty($el['Identifier']) && !empty($el["isService"]));
              $appsWithIcon = [];

              foreach ($appsWithIconToFilter as $item)
                $appsWithIcon[$item['Identifier']] = $item;

              $appsInDatabase = DB::table('appsIcons')->select('iconBase64', 'identifier')
                ->whereIn('identifier', array_map(fn($el) => $el['Identifier'], $appsWithIcon))
                ->where('osType', 'android')
                ->get();
              foreach ($appsInDatabase as $appInDb) {
                $appsWithIcon[$appInDb->identifier]['icon'] = $appInDb->iconBase64 ?? null;
                $appsWithIcon[$appInDb->identifier]['alreadyChecked'] = true;
              }
              foreach (array_filter($appsWithIcon, fn($el) => empty($el['alreadyChecked'])) as $iconToFetch)
                FetchIconApp::dispatch($iconToFetch['Identifier'], 'ANDROID');

              $appsSerialized = [];
              foreach ($appsWithIcon as $elemToInsert) {
                $elemToInsert['permissionsScore'] = SecurityPostureDeviceHandler::calcAppPermissionScore($elemToInsert['runtimePermissions'] ?? []);
                $appsSerialized [] = $elemToInsert;
              }
              foreach ($appsWithoutIcon as $elemToInsert)
                $appsSerialized [] = $elemToInsert;

              return $appsSerialized;
            } else if ($device->osType == 'ios') {
              $appsWithIcon = [];
              foreach ($apps as $item)
                $appsWithIcon[$item['Identifier']] = $item;

              $appsInDatabase = DB::table('appsIcons')->select('iconBase64', 'identifier')
                ->whereIn('identifier', array_map(fn($el) => $el['Identifier'], $appsWithIcon))
                ->where('osType', 'ios')
                ->get();
              foreach ($appsInDatabase as $appInDb) {
                $appsWithIcon[$appInDb->identifier]['icon'] = $appInDb->iconBase64 ?? null;
                $appsWithIcon[$appInDb->identifier]['alreadyChecked'] = true;
              }
              foreach (array_filter($appsWithIcon, fn($el) => empty($el['alreadyChecked'])) as $iconToFetch)
                FetchIconApp::dispatch($iconToFetch['Identifier'], 'IOS');

              $appsSerialized = [];
              foreach ($appsWithIcon as $elemToInsert)
                $appsSerialized [] = $elemToInsert;

              return $appsSerialized;
            } else return [];
          } else return [];
        });
      return $promise;

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return [];
    }
  }

  public function getDeviceGroups($filters)
  {
    try {
      $clientsDatabase = Client::on($this->_getDbConnection())->whereIn('id', $filters['clients'])->get();
      $responseTokens = Http::pool(fn(Pool $pool) => collect($clientsDatabase)->map(fn($client) => $pool->as($client['id'])
        ->asForm()
        ->post('https://' . $client['host'] . '/' . $client['baseUrl'] . '/api/latest/mssp/authorize', [
            'grant_type' => 'client_credentials',
            'client_id' => config('app.client_id'),
            'client_secret' => config('app.client_secret'),
            'scope' => 'data.read',
          ]
        )
      )
      );
      $validTokenResponses = [];
      foreach ($responseTokens as $clientId => $responseToken) {
        if (($responseToken instanceof Response) && $responseToken->ok()) {
          $tokenValid = $responseToken->json();
          $tokenValid['clientId'] = $clientId;
          foreach ($clientsDatabase as $client) {
            if ($client['id'] == $tokenValid['clientId']) {
              $tokenValid['host'] = $client['host'];
              $tokenValid['baseUrl'] = $client['baseUrl'];
              break;
            }
          }
          $validTokenResponses[] = $tokenValid;
        }
      };
      $deviceGroups = Http::pool(fn(Pool $pool) => collect($validTokenResponses)
        ->map(fn($el) => $pool->as($el['clientId'])
          ->withToken($el['access_token'])
          ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/groups')
        ));
      $payload = [];
      foreach ($deviceGroups as $keyClientId => $response) {
        if (($response instanceof Response) && $response->ok()) {
          $groups = $response->json();
          if (empty($groups))
            continue;
          foreach ($groups as $group)
            $payload [] = $group;
        }
      }

      return $payload;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return [];
    }
  }

  /**
   * numero di app installate V
   * tipologia di app installata
   * uno score parallelo che viene calcolato valutando "permessi richiesti di base dalle singole app" e "runtime permission concesse ad ogni singola app" V
   * certificati scaduti
   * restrizioni critiche abilitate
   * eventi di navigazione o scan critici negli ultimi giorni V
   * passcode mancante o non compliant
   * integrità
   * numero di cve in base alla gravità
   * compliancy delle regole impostate
   * aggiornamenti disponibili
   */
}
