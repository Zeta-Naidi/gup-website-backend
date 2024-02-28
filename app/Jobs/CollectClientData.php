<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\EventType;
use App\Models\Reseller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CollectClientData implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $dbConnection;
  private $dataType;

  /**
   * Create a new job instance.
   * @param array $dbConnection array of strings to connect to, if empty the job will take all connections valid from database.php
   * @param string $dataType Type of fetcher to execute
   * @return void
   */
  public function __construct($dbConnection = [], $dataType = '')
  {
    $this->dbConnection = $dbConnection;
    $this->dataType = $dataType;
  }


  /**
   * Execute the job.
   * @return void
   */
  public function handle()
  {
    if (!$this->_checkDataToCollect($this->dataType))
      return;
    if (empty($this->dbConnection)) {
      $allDistributorConnections = array_filter(config('database.connections'),fn($el) => isset($el['type']) && $el['type'] == 'distributor');
      $allConnectionsFiltered = [];
      foreach ($allDistributorConnections as $key => $connection) {
        if ($key != config('database.default') && $key != 'sqlite_testing')
          $allConnectionsFiltered[] = $key;
      }
    } else
      $allConnectionsFiltered = $this->dbConnection;
    switch ($this->dataType) {
      case 'CLIENT':
        $this->_listClients($allConnectionsFiltered);
        break;
      case 'EVENT':
        $this->_listEvents($allConnectionsFiltered);
        break;
      case 'EVENT_TYPE':
        $this->_listEventTypes($allConnectionsFiltered);
        break;
      case 'DEVICE':
        $this->_listDevices($allConnectionsFiltered);
        break;
      case 'NETWORK_ACTIVITY':
        $this->_listNetworkActivities($allConnectionsFiltered);
        break;
      case 'APP_USAGE':
        $this->_listAppUsages($allConnectionsFiltered);
        break;
      default:
        break;
    }
  }

  private function _listClients($connectionsToCall = [])
  {

    try {
      $responseTokens = Http::pool(fn(Pool $pool) => collect($connectionsToCall)->map(fn($el) => $pool->asForm()->post('https://portal.chimpa.eu/services/api/v20/mssp_token', [
        'grant_type' => 'client_credentials',
        'client_id' => config('app.client_id'),
        'client_secret' => config('app.client_secret'),
        'scope' => 'data.read',
      ])
      )
      );
      $clientsFromCloud = Http::pool(fn(Pool $pool) => collect($connectionsToCall)->map(fn($el) => $pool->as($el)
        ->withToken(array_pop($responseTokens)->json()['access_token'])
        ->post('https://portal.chimpa.eu/services/api/v20/mssp_customers',
          ['distributorId' => explode("_", $el)[2]])
      )
      );
      foreach ($connectionsToCall as $connection) {
        $clientsToAdd = [];
        $idsOfClientsPresent = [];
        //Cycle through Clients in DB to check which clients have to be deleted, if not present in clients array from cloud
        foreach ($clientsFromCloud[$connection]->json() as $clientFromChimpa) {
          $result = Client::on($connection)->where('chimpaClientId', $clientFromChimpa['clientId'])->first();
          if (!$result)
            $clientsToAdd[] = $clientFromChimpa;
          $idsOfClientsPresent[] = $clientFromChimpa['clientId'];
        }
        //Soft Deleting clients
        Client::on($connection)->whereNotIn('chimpaClientId', $idsOfClientsPresent)->delete();

        $clientsToInsert = [];
        foreach ($clientsToAdd as $clientToAdd) {
          $resellerInstance = Reseller::on($connection)->where('chimpaResellerId', $clientToAdd["resellerId"])->first();
          if (!$resellerInstance) {
            $resellerInstance = Reseller::on($connection)->create([
              "chimpaResellerId" => $clientToAdd["resellerId"],
              "name" => $clientToAdd["resellerName"],
            ]);
          }
          //$clientToAdd = $this->_setCoords($clientToAdd);
          $clientsToInsert[] = [
            "chimpaClientId" => $clientToAdd['clientId'],
            "resellerId" => $resellerInstance->id,
            "baseUrl" => $clientToAdd['baseUrl'],
            "host" => $clientToAdd['host'],
            "companyName" => $clientToAdd['companyName'],
            //"lat" => $clientToAdd['lat'],
            //"lon" => $clientToAdd['lon'],
            "countryCode" => $clientToAdd['countryCode'],
            "phone" => $clientToAdd['phone'] ?? null,
            "email" => $clientToAdd['email'] ?? null,
          ];
        }
        DB::connection($connection)->table('clients')->insert($clientsToInsert);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }

  }

  private function _listEvents($connectionsToCall = [])
  {
    $startingTime = date("Y-m-d H:i:s");
    foreach ($connectionsToCall as $connection) {
      try {
        $validTokenResponses = $this->_getAccessTokens($connection);
        $security_events = Http::pool(fn(Pool $pool) => collect($validTokenResponses)
          ->filter(fn($el) => (isset($el["devicesLastUpdate"]) && !$this->_dateOneWeekOld($el['devicesLastUpdate']))) //wait for fetch devices to be almost finished if it's first fetch
          ->map(fn($el) => $pool->as($el['clientId'])
            ->withToken($el['access_token'])
            ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/security_events', [
              'filters' => isset($el['eventsLastUpdate']) ?
                $this->_calcFilterDateEvents($el['eventsLastUpdate']) :
                [
                  ["key" => "dsn.updatedAt", "operator" => ">", "value" => strtotime('-60 days'), "isDate" => true],
                  ["key" => "dsn.updatedAt", "operator" => "<=", "value" => strtotime('-53 days'), "isDate" => true],
                ]
            ])
          ));
        foreach ($security_events as $keyClientId => $responseEvents) {
          //CASE SERVER DOWN
          if (!($responseEvents instanceof Response) || !$responseEvents->ok())
            continue;
          $responseEventsSerialized = $responseEvents->json();
          $clientDB = Client::on($connection)->where('id', $keyClientId)->first();
          //CASE NO NEW EVENTS
          if (empty($responseEventsSerialized)) {
            //CASE FIRST FETCH
            if (!$clientDB->eventsLastUpdate) {
              $clientDB->eventsLastUpdate = date("Y-m-d H:i:s", strtotime('-53 days', strtotime($startingTime)));
              $clientDB->save();
              continue;
            }
            //CASE DATE OLDER THAN ONE WEEK
            else if ($this->_dateOneWeekOld($clientDB->eventsLastUpdate)) {
              $clientDB->eventsLastUpdate = date("Y-m-d H:i:s", strtotime('+7 days', strtotime($clientDB->eventsLastUpdate)));
              $clientDB->save();
              continue;
            }
            //CASE DATE NEWER THAN ONE WEEK
            else {
              $clientDB->eventsLastUpdate = $startingTime;
              $clientDB->save();
              continue;
            }
          }
          if (count($responseEventsSerialized['dataset']) < 100) {
            dispatch(new EventFetcher($connection, 'INSERT_EVENTS_INTERVAL',
              [
                'events' => $responseEventsSerialized['dataset'],
                'clientId' => $keyClientId,
                'resellerId' => $clientDB->resellerId
              ]));
          } else {
            $offset = 0;
            $lengthInterval = 100;
            while ($offset < count($responseEventsSerialized['dataset'])) {
              dispatch(new EventFetcher($connection, 'INSERT_EVENTS_INTERVAL',
                  [
                    'events' => array_slice($responseEventsSerialized['dataset'], $offset, $lengthInterval),
                    'clientId' => $keyClientId,
                    'resellerId' => $clientDB->resellerId
                  ])
              );
              $offset += $lengthInterval;
              if ($offset + $lengthInterval > count($responseEventsSerialized['dataset'])) {
                dispatch(new EventFetcher($connection, 'INSERT_EVENTS_INTERVAL',
                    [
                      'events' => array_slice($responseEventsSerialized['dataset'], $offset),
                      'clientId' => $keyClientId,
                      'resellerId' => $clientDB->resellerId
                    ])
                );
                break;
              }
            }
          }
        }
      } catch
      (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }

    }
  }

  private function _listEventTypes($connectionsToCall = [])
  {
    foreach ($connectionsToCall as $connection) {
      $validTokenResponses = $this->_getAccessTokens($connection);
      if (empty($validTokenResponses))
        continue;
      $el = array_pop($validTokenResponses);
      $event_types = Http::withToken($el['access_token'])
        ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/list_event_types');
      if (($event_types instanceof Response) && $event_types->ok()) {
        try {
          foreach ($event_types->json() as $key => $value) {
            if (!EventType::on($connection)->where('key', $key)->first()) {
              EventType::on($connection)->create([
                "key" => $key,
                "value" => $value,
              ]);
            }
          }
        } catch (\Exception $e) {
          \App\Exceptions\CatchedExceptionHandler::handle($e);
        }
      }
    }
  }

  private function _listDevices($connectionsToCall = [])
  {
    $startingTime = date("Y-m-d H:i:s");
    foreach ($connectionsToCall as $connection) {
      $validTokenResponses = $this->_getAccessTokens($connection);

      $devicesFromCloud = Http::pool(fn(Pool $pool) => collect($validTokenResponses)->map(fn($el) => $pool->as($el['clientId'])
        ->withToken($el['access_token'])
        ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/devices',
          isset($el['devicesLastUpdate']) ?
            $this->_calcFilterDateDevices($el['devicesLastUpdate']) :
            ['maximumUpdateDate' => date("Y-m-d H:i:s", strtotime('-53 days', strtotime($startingTime)))]
        )
      )
      );
      foreach ($devicesFromCloud as $keyClientId => $deviceResponse) {

        try {
          if (!($deviceResponse instanceof Response) || !$deviceResponse->ok())
            continue;
          $deviceResponseSerialized = $deviceResponse->json();

          //CASE NO NEW DEVICES
          $clientDB = Client::on($connection)->where('id', $keyClientId)->first();
          if (empty($deviceResponseSerialized)) {
            if (!$clientDB->devicesLastUpdate) {
              $clientDB->devicesLastUpdate = date("Y-m-d H:i:s", strtotime('-53 days', strtotime($startingTime)));
              $clientDB->save();
              continue;
            } else if ($this->_dateOneWeekOld($clientDB->devicesLastUpdate)) {
              $clientDB->devicesLastUpdate = date("Y-m-d H:i:s", strtotime('+7 days', strtotime($clientDB->devicesLastUpdate)));
              $clientDB->save();
              continue;
            } else {
              $clientDB->devicesLastUpdate = $startingTime;
              $clientDB->save();
              continue;
            }
          }

          $clientDB->devicesLastUpdate = !$clientDB->devicesLastUpdate ?
            date("Y-m-d H:i:s", strtotime('-53 days', strtotime($startingTime))) :
            ($this->_dateOneWeekOld($clientDB->devicesLastUpdate) ?
              date("Y-m-d H:i:s", strtotime('+7 days', strtotime($clientDB->devicesLastUpdate))) :
              $startingTime
            );
          $clientDB->save();

          if (count($deviceResponseSerialized) < 100) {
            dispatch(new DeviceFetcher($connection, 'INSERT_DEVICES_INTERVAL',
              [
                'devices' => $deviceResponseSerialized,
                'clientId' => $keyClientId
              ]));
          } else {
            $offset = 0;
            $lengthInterval = 100;
            while ($offset < count($deviceResponseSerialized)) {
              dispatch(new DeviceFetcher($connection, 'INSERT_DEVICES_INTERVAL',
                  [
                    'devices' => array_slice($deviceResponseSerialized, $offset, $lengthInterval),
                    'clientId' => $keyClientId
                  ])
              );
              $offset += $lengthInterval;
              if ($offset + $lengthInterval > count($deviceResponseSerialized)) {
                dispatch(new DeviceFetcher($connection, 'INSERT_DEVICES_INTERVAL',
                    [
                      'devices' => array_slice($deviceResponseSerialized, $offset),
                      'clientId' => $keyClientId
                    ])
                );
                break;
              }
            }
          }

        } catch (\Exception $e) {
          \App\Exceptions\CatchedExceptionHandler::handle($e);
        }
      }
    }
  }

  private function _listNetworkActivities($connectionsToCall = [])
  {
    foreach ($connectionsToCall as $connection) {

      try {
        $validTokenResponses = $this->_getAccessTokens($connection);

        $networkActivities = Http::pool(fn(Pool $pool) => collect($validTokenResponses)->map(fn($el) => $pool->as($el['clientId'])
          ->withToken($el['access_token'])
          ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/network_activity', [
            'minimumUpdateDate' => $el['networkActivitiesLastUpdate'] ?? null
          ])
        )
        );

        foreach ($networkActivities as $keyClientId => $responseObj) {
          $responseNetworkActivitySerialized = $responseObj->json();

          if (empty($responseNetworkActivitySerialized) || empty($responseNetworkActivitySerialized['network_activity']))
            continue;
          if (count($responseNetworkActivitySerialized['network_activity']) < 100) {
            dispatch(new NetworkActivityFetcher($connection, 'INSERT_NETWORK_ACTIVITIES',
              [
                'payload' => $responseNetworkActivitySerialized['network_activity'],
                'clientId' => $keyClientId
              ]));
          } else {
            $offset = 0;
            $lengthInterval = 100;
            while ($offset < count($responseNetworkActivitySerialized['network_activity'])) {
              dispatch(new NetworkActivityFetcher($connection, 'INSERT_NETWORK_ACTIVITIES',
                  [
                    'payload' => array_slice($responseNetworkActivitySerialized['network_activity'], $offset, $lengthInterval),
                    'clientId' => $keyClientId
                  ])
              );
              $offset += $lengthInterval;
              if ($offset + $lengthInterval > count($responseNetworkActivitySerialized['network_activity'])) {
                dispatch(new NetworkActivityFetcher($connection, 'INSERT_NETWORK_ACTIVITIES',
                    [
                      'payload' => array_slice($responseNetworkActivitySerialized['network_activity'], $offset),
                      'clientId' => $keyClientId
                    ])
                );
                break;
              }
            }
          }
          $clientDB = Client::on($connection)->where('id', $keyClientId)->first();
          DB::connection($connection)->transaction(function () use ($clientDB) {
            $clientDB->networkActivitiesLastUpdate = new \DateTime();
            $clientDB->save();
          });
        }
      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }

    }

  }

  private function _listAppUsages($connectionsToCall = [])
  {
    foreach ($connectionsToCall as $connection) {

      try {
        $validTokenResponses = $this->_getAccessTokens($connection);

        $appUsages = Http::pool(fn(Pool $pool) => collect($validTokenResponses)->map(fn($el) => $pool->as($el['clientId'])
          ->withToken($el['access_token'])
          ->post('https://' . $el['host'] . '/' . $el['baseUrl'] . '/api/latest/mssp/app_usage', [
            'minimumUpdateDate' => $el['appUsagesLastUpdate'] ?? null
          ])
        )
        );

        foreach ($appUsages as $keyClientId => $responseObj) {
          $responseAppUsageSerialized = $responseObj->json();
          if (empty($responseAppUsageSerialized) || empty($responseAppUsageSerialized['network_activity']))
            continue;
          if (count($responseAppUsageSerialized['network_activity']) < 100) {
            dispatch(new AppUsageFetcher($connection, 'INSERT_APP_USAGES',
              [
                'payload' => $responseAppUsageSerialized['network_activity'],
                'clientId' => $keyClientId
              ]));
          } else {
            $offset = 0;
            $lengthInterval = 100;
            while ($offset < count($responseAppUsageSerialized['network_activity'])) {
              dispatch(new AppUsageFetcher($connection, 'INSERT_APP_USAGES',
                  [
                    'payload' => array_slice($responseAppUsageSerialized['network_activity'], $offset, $lengthInterval),
                    'clientId' => $keyClientId
                  ])
              );
              $offset += $lengthInterval;
              if ($offset + $lengthInterval > count($responseAppUsageSerialized['network_activity'])) {
                dispatch(new AppUsageFetcher($connection, 'INSERT_APP_USAGES',
                    [
                      'payload' => array_slice($responseAppUsageSerialized['network_activity'], $offset),
                      'clientId' => $keyClientId
                    ])
                );
                break;
              }
            }
          }
          $clientDB = Client::on($connection)->where('id', $keyClientId)->first();
          DB::connection($connection)->transaction(function () use ($clientDB) {
            $clientDB->appUsagesLastUpdate = new \DateTime();
            $clientDB->save();
          });
        }
      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }

    }

  }

  private function _checkDataToCollect($dataType)
  {
    return $dataType == 'EVENT' || $dataType == 'EVENT_TYPE' || $dataType == 'CLIENT' || $dataType == 'DEVICE' || $dataType == 'NETWORK_ACTIVITY' || $dataType == 'APP_USAGE';
  }

  private function _dateOneWeekOld($timestamp)
  {
    $now = new \DateTime();
    $target = new \DateTime($timestamp);
    $interval = $now->diff($target);
    $days = (int)$interval->format('%a');
    return $days >= 7;
  }

  private function _calcFilterDateEvents($eventLastUpdate)
  {
    if ($this->_dateOneWeekOld($eventLastUpdate)) {
      return
        [
          ["key" => "dsn.updatedAt", "operator" => ">", "value" => strtotime($eventLastUpdate), "isDate" => true],
          ["key" => "dsn.updatedAt", "operator" => "<=", "value" => strtotime('+7 days', strtotime($eventLastUpdate)), "isDate" => true],
        ];
    } else {
      return
        [
          [
            "key" => "dsn.updatedAt", "operator" => ">",
            "value" => strtotime($eventLastUpdate),
            "isDate" => true
          ]
        ];
    }
  }

  private function _calcFilterDateDevices($deviceLastUpdate)
  {
    if ($this->_dateOneWeekOld($deviceLastUpdate)) {
      return
        [
          "minimumUpdateDate" => $deviceLastUpdate,
          "maximumUpdateDate" => date("Y-m-d H:i:s", strtotime('+7 days', strtotime($deviceLastUpdate)))
        ];
    } else {
      return
        [
          "minimumUpdateDate" => $deviceLastUpdate
        ];
    }
  }

  /**
   * @param $connection
   * @return array containing the necessary information.
   *  $array = [
   *    "access_token" => (string) -e.g. a9c31f872c73b7377c93f72b66845dc18f765085,
   *    "expires_in" => (int) -e.g. 3600,
   *    "token_type" => (string) -e.g. Bearer,
   *    "scope" => (string) -e.g. data.read,
   *    "clientId" => (int) -e.g. 1,
   *    "host" => (string) -e.g. blabla.chimpa.eu,
   *    "baseUrl" => (string) -e.g. testmdm,
   *    "eventsLastUpdate" => (string | null) -e.g. null,
   *    "devicesLastUpdate" => (string | null) -e.g. 2022-11-15 09:40:05
   *  ]
   */
  private function _getAccessTokens($connection)
  {
    try {
      $clientsDatabase = Client::on($connection)->select([
        'id',
        'host',
        'baseUrl',
        'eventsLastUpdate',
        'devicesLastUpdate',
        'appUsagesLastUpdate',
        'networkActivitiesLastUpdate',
      ])->get()->toArray();

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
      foreach ($responseTokens as $clientID => $responseToken) {
        if (($responseToken instanceof Response) && $responseToken->ok() && $responseToken->json()) {
          $tokenValid = $responseToken->json();
          $tokenValid['clientId'] = $clientID;
          foreach ($clientsDatabase as $client) {
            if ($client['id'] == $tokenValid['clientId']) {
              $tokenValid['host'] = $client['host'];
              $tokenValid['baseUrl'] = $client['baseUrl'];
              $tokenValid['eventsLastUpdate'] = $client['eventsLastUpdate'];
              $tokenValid['devicesLastUpdate'] = $client['devicesLastUpdate'];
              $tokenValid['appUsagesLastUpdate'] = $client['appUsagesLastUpdate'];
              $tokenValid['networkActivitiesLastUpdate'] = $client['networkActivitiesLastUpdate'];
              break;
            }
          }
          $validTokenResponses[] = $tokenValid;
        }
      };
      return $validTokenResponses;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }

  private function _setCoords($client): array
  {
    $returnClient = $client;
    try {
      $response = Http::get('https://nominatim.openstreetmap.org/search', [
        'country' => $client['country'],
        'city' => $client['city'],
        'format' => 'json',
      ]);
      if (($response instanceof Response) && $response->ok()) {
        $responseSerialized = $response->json();
        $returnClient['lat'] = $responseSerialized[0]['lat'];
        $returnClient['lon'] = $responseSerialized[0]['lon'];
        return $returnClient;
      } else
        return [...$returnClient, 'lon' => null, 'lat' => null];
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return [...$returnClient, 'lon' => null, 'lat' => null];;
    }
  }
}
