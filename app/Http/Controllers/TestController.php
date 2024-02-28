<?php

namespace App\Http\Controllers;

use App\Exceptions\CatchedExceptionHandler;
use App\Jobs\EventFetcher;
use App\Models\Client;
use App\Models\Device;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Reseller;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TestController extends Controller
{
  private $devices = [];
  private $androidNames = [
    "Samsung Galaxy S21",
    "Samsung Galaxy Note 20",
    "Samsung Galaxy A52",
    "Google Pixel 6",
    "Google Pixel 5a",
    "OnePlus 9 Pro",
    "OnePlus Nord",
    "Xiaomi Mi 11",
    "Xiaomi Redmi Note 10 Pro",
    "Oppo Find X3 Pro",
    "Realme X7 Pro",
    "Vivo X60 Pro",
    "Moto G Power",
    "LG G8 ThinQ",
    "HTC U12+",
    "Huawei P40 Pro",
    "Huawei Mate 40 Pro",
    "ZTE Axon 11",
    "Sony Xperia 1 III",
    "Nokia 9.3 PureView",
    "Samsung Galaxy S20",
    "Samsung Galaxy Note 10",
    "Samsung Galaxy A51",
    "Google Pixel 4a",
    "OnePlus 8 Pro",
    "OnePlus 7T",
    "Xiaomi Mi 10",
    "Xiaomi Redmi Note 9 Pro",
    "Oppo Find X2 Pro",
    "Realme X2 Pro",
    "Vivo X50 Pro",
    "Moto G Stylus",
    "LG G7 ThinQ",
    "HTC U11+",
    "Huawei P30 Pro",
    "Huawei Mate 30 Pro",
    "ZTE Axon 10 Pro",
    "Sony Xperia 1 II",
    "Nokia 9 PureView",
    "Samsung Galaxy S10",
    "Samsung Galaxy Note 9",
    "Samsung Galaxy A50",
    "Google Pixel 3a",
    "OnePlus 7 Pro",
    "OnePlus 6T",
    "Xiaomi Mi 9",
    "Xiaomi Redmi Note 8 Pro",
    "Oppo Reno 10x Zoom",
    "Realme X2",
    "Vivo X27 Pro",
    "Moto G7 Power",
    "LG G6",
    "HTC U12 Life",
    "Huawei P20 Pro",
    "Huawei Mate 20 Pro",
    "ZTE Axon 9 Pro",
    "Sony Xperia XZ3",
    "Nokia 8.1",
    "Samsung Galaxy S9",
    "Samsung Galaxy Note 8",
    "Samsung Galaxy A40",
    "Google Pixel 2 XL",
    "OnePlus 5T",
    "OnePlus 3T",
    "Xiaomi Mi 8",
    "Xiaomi Redmi Note 7 Pro",
    "Oppo Find X",
    "Realme U1",
    "Vivo NEX S",
    "Moto G5 Plus",
    "LG V30",
    "HTC U11",
    "Huawei P10 Plus",
    "Huawei Mate 10 Pro",
    "ZTE Axon 7",
    "Sony Xperia XZ2",
    "Nokia 7.1",
  ];
  private $windowsNames = [
    "PC desktop",
    "Laptop",
    "Tablet",
    "2-in-1",
    "All-in-One",
    "Ultrabook",
    "Workstation",
    "Gaming laptop",
    "Convertibile",
    "Netbook",
    "Hybride",
    "Smartbook",
    "PDA",
    "Pocket PC",
    "Mini PC",
    "Stick PC",
    "Notebook",
    "Dispositivo portatile",
    "Dispositivo ibrido",
    "Dispositivo touch screen",
    "Surface Pro",
    "Surface Book",
    "Surface Laptop",
    "Surface Studio",
    "Dell XPS",
    "HP Spectre",
    "Lenovo ThinkPad",
    "ASUS ZenBook",
    "Acer Swift",
    "Razer Blade",
    "Microsoft Surface Go",
    "Microsoft Surface Hub",
    "Microsoft Surface Dock",
    "Microsoft Surface Headphones",
    "HP Elitebook",
    "Lenovo Yoga",
    "Dell Inspiron",
    "ASUS Chromebook",
    "HP Stream",
    "Lenovo Ideapad",
    "Acer Aspire",
    "Microsoft Surface Dial",
    "Microsoft Surface Pen",
    "Microsoft Surface Keyboard",
    "Microsoft Surface Mouse",
    "HP Pavilion",
    "Lenovo Legion",
    "Dell Latitude",
    "ASUS VivoBook",
    "Acer Chromebook Spin",
    "HP Envy",
    "Lenovo ThinkCentre",
    "Dell Optiplex",
    "ASUS Chromebook Flip",
    "Acer Chromebook Tab 10",
    "Microsoft Surface Pro X",
    "Microsoft Surface Pro 7",
    "Microsoft Surface Pro 6",
    "Microsoft Surface Pro 5",
    "Dell Streak",
    "ASUS Fonepad",
    "Acer Iconia Tab",
    "HP Stream 7",
    "Lenovo Yoga Book",
    "Dell Venue 11 Pro",
    "ASUS Transformer Book",
    "Acer Iconia W4",
    "HP Stream 8",
    "Lenovo Yoga Tablet",
    "Dell Latitude 10",
    "ASUS VivoTab",
    "Acer Iconia A1",
    "HP Omni 10",
    "Lenovo ThinkPad 10",
    "Dell Venue 8 Pro",
    "ASUS Transformer Pad",
    "Acer Iconia Talk S",
  ];
  private $iosNames = [
    "John's iPhone XS Max",
    "Jane's iPhone SE",
    "Michael's iPhone 8 Plus",
    "Emily's iPhone 7",
    "David's iPhone 6S",
    "Sarah's iPhone 5S",
    "Chris's iPhone XR",
    "Amy's iPhone X",
    "Brian's iPhone 8",
    "Tom's iPhone 7 Plus",
    "Jennifer's iPhone 6",
    "Robert's iPhone XS",
    "Emily's iPhone SE 2020",
    "William's iPhone 11 Pro Max",
    "Linda's iPhone 11",
    "James's iPhone X",
    "Jane's iPhone 11 Pro",
    "Michael's iPhone 6S Plus",
    "Emily's iPhone 8",
    "David's iPhone 7",
    "Sarah's iPhone XR",
    "Chris's iPhone 6",
    "Amy's iPhone 11",
    "Brian's iPhone 7 Plus",
    "Tom's iPhone 8 Plus",
    "Jennifer's iPhone XS",
    "Robert's iPhone SE 2020",
    "Emily's iPhone 11 Pro Max",
    "William's iPhone X",
    "Linda's iPhone 6S",
    "James's iPhone 7",
    "Jane's iPhone 8",
    "Michael's iPhone XR",
    "Emily's iPhone 7 Plus",
    "David's iPhone 6",
    "Sarah's iPhone 11",
    "Chris's iPhone 8 Plus",
    "Amy's iPhone XS",
    "Brian's iPhone 7",
    "Tom's iPhone 6S",
    "Jennifer's iPhone XR",
    "Robert's iPhone 11 Pro Max",
    "Emily's iPhone 8",
    "William's iPhone 7",
    "Linda's iPhone XS",
    "James's iPhone 6",
    "Jane's iPhone 7 Plus",
    "Michael's iPhone XR",
    "Emily's iPhone 8 Plus",
    "David's iPhone 7",
    "Sarah's iPhone XS",
    "Chris's iPhone 6S",
    "Amy's iPhone 11 Pro Max",
    "Brian's iPhone 7 Plus",
    "Tom's iPhone 8",
    "Jennifer's iPhone XR",
    "Robert's iPhone 7",
    "Emily's iPhone 6S Plus",
    "William's iPhone 8 Plus",
    "Linda's iPhone XS",
    "James's iPhone 7",
    "Jane's iPhone XR",
    "Michael's iPhone 8",
    "Emily's iPhone 7 Plus",
    "David's iPhone 6S",
    "Sarah's iPhone XS",
    "Chris's iPhone 7",
    "Amy's iPhone XR",
    "Brian's iPhone 8 Plus",
    "iPhone 12 Pro Max",
    "iPhone 12 mini",
    "iPhone SE (2020)",
    "iPad Air (4th generation)",
    "iPad Pro (11-inch)",
    "MacBook Pro (16-inch)",
  ];

  public function test()
  {
    if (App::environment('production'))
      return;

    $userAuthenticated = auth()->user();

    $strJsonFileContents = file_get_contents(base_path() . "/app/Utils/eventsToBeGeneratedLong.json");
    $randomEvents = json_decode($strJsonFileContents, true);
    $devices = Device::on($userAuthenticated->nameDatabaseConnection)
      ->join('clients','clients.id','=','devices.clientId')
      ->whereNull('clients.deleted_at')
      ->select(['clientId', 'serialNumber', 'osType'])->get()
      ->toArray();
    $lastChimpaEventId = Event::on($userAuthenticated->nameDatabaseConnection)
      ->orderBy('chimpaEventId', 'desc')->first();
    $lastChimpaEventId = $lastChimpaEventId->chimpaEventId;
    $rowsToReturn = [];
    $date = new \DateTime();
    $newEvents = rand(1, 5);
    for ($j = 0; $j < $newEvents; $j++) {
      $indexEvent = rand(0, 1999);
      $eventChosen = $randomEvents[$indexEvent];
      if (!empty($eventChosen['os']))
        $devicesFiltered = array_values(array_filter($devices, fn($el) => in_array($el['osType'], $eventChosen['os'])));
      else
        $devicesFiltered = $devices;
      $indexDevice = rand(0, count($devicesFiltered) - 1);
      $lastChimpaEventId++;
      $newEvent = (new Event())->setConnection($userAuthenticated->nameDatabaseConnection);
      $newEvent->chimpaEventId = $lastChimpaEventId;
      $newEvent->clientId = $devicesFiltered[$indexDevice]["clientId"];
      $newEvent->deviceSerialNumber = $devicesFiltered[$indexDevice]["serialNumber"];
      $newEvent->type = $eventChosen["type"];
      $newEvent->score = $eventChosen["score"];
      $newEvent->criticalityLevel = $this->calcCriticalityLevel($eventChosen["score"]);
      $newEvent->detectionDate = $date->format('Y-m-d H:i:s');
      $newEvent->description = $eventChosen["description"];
      $newEvent->docs = $eventChosen["docs"];
      $newEvent->subject = $eventChosen["subject"] ?? null;
      $newEvent->remediationAction = $this->_calcRemediationAction($eventChosen["type"], $devicesFiltered[$indexDevice]["osType"]);
      if ($newEvent->save()) {
        $rowsToReturn[] = Event::on($userAuthenticated->nameDatabaseConnection)
          ->with(['client', 'device', 'event_type'])->find($newEvent->id);
      }
    }
    //Filter rowstoreturn based on roles
    if (isset($userAuthenticated->rolesUser)) {
      $rowsFiltered = $rowsToReturn;
      if (isset($userAuthenticated->rolesUser->clientsFilter))
        $rowsFiltered = array_filter($rowsFiltered, fn($eventToCheck) => in_array($eventToCheck->clientId, $userAuthenticated->rolesUser->clientsFilter));
      if (isset($userAuthenticated->rolesUser->eventTypeFilter))
        $rowsFiltered = array_filter($rowsFiltered, fn($eventToCheck) => in_array($eventToCheck->type, $userAuthenticated->rolesUser->eventTypeFilter));
      if (isset($userAuthenticated->rolesUser->scoreFilter))
        $rowsFiltered = array_filter($rowsFiltered, fn($eventToCheck) => in_array($eventToCheck->criticalityLevel, $userAuthenticated->rolesUser->scoreFilter));
      return response(["success" => true, 'payload' => $rowsFiltered]);
    }
    return response(["success" => true, 'payload' => $rowsToReturn]);
  }


  public function test2()
  {
    try {
      $strJsonFileContents = file_get_contents(base_path() . "/app/Utils/eventsToBeGeneratedLong.json");
      $randomEvents = json_decode($strJsonFileContents, true);
      $devices = Device::on('testing_distributor_2_d3tGk')->select(['clientId', 'serialNumber', 'osType'])->get()->toArray();
      $lastChimpaEventId = Event::on('distributor_2_d3tGk')
        ->orderBy('chimpaEventId', 'desc')->first();
      if (!$lastChimpaEventId)
        $lastChimpaEventId = 0;
      else
        $lastChimpaEventId = $lastChimpaEventId->chimpaEventId;
      for ($i = 0; $i < 60; $i++) {
        $rowsToInsert = [];
        $date = new \DateTime();
        $date->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
        $date->modify("-" . $i . " day");
        for ($j = 0; $j < 500; $j++) {
          $indexEvent = rand(0, 1999);
          $indexDevice = rand(0, count($devices) - 1);
          $eventChosen = $randomEvents[$indexEvent];
          $lastChimpaEventId++;
          $rowsToInsert[] = [
            "chimpaEventId" => $lastChimpaEventId,
            "clientId" => $devices[$indexDevice]["clientId"],
            "deviceSerialNumber" => $devices[$indexDevice]["serialNumber"],
            "type" => $eventChosen["type"],
            "score" => $eventChosen["score"],
            "criticalityLevel" => $this->calcCriticalityLevel($eventChosen["score"]),
            "detectionDate" => $date->format('Y-m-d'),
            "description" => $eventChosen["description"],
            "docs" => $eventChosen["docs"],
            "remediationAction" => $this->_calcRemediationAction($eventChosen["type"], $devices[$indexDevice]["osType"])
          ];
        }
        DB::connection('distributor_2_d3tGk')->table('events')
          ->insert($rowsToInsert);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }

  }

  private function _calcRemediationAction($eventType, $osDevice)
  {
    // TODO verify osx when implemented
    if ($eventType === 22)
      return 'scheduleOsUpdate';
    else if ($eventType === 6)
      return 'wipe';
    else if ($eventType === 4)
      return 'ENABLE_EMERGENCY_MODE';
    else if ($eventType === 2)
      return 'REMOVE_CERTIFICATE';
    else if ($eventType === 27)
      return 'reconnectGooglePlayManagedAccount';
    else if ($osDevice === 'android' && ($eventType === 19 || $eventType === 20))
      return 'REMOVE_APPS_ANDROID';
    else if ($osDevice === 'ios' && ($eventType === 19 || $eventType === 20))
      return 'REMOVE_APPS_IOS';
    else if ($osDevice === 'ios' && $eventType === 18)
      return 'ADD_APP_IOS';
    else if ($osDevice === 'android' && $eventType === 18)
      return 'installApp';
    else return null;
  }


  public function test3()
  {
    $rowsToInsert = [];
    $clients = Client::on('testing_distributor_2_d3tGk')->select(['id'])->get()->toArray();
    $devices = Device::on('testing_distributor_2_d3tGk')->get()->toArray();

    foreach ($devices as $device) {
      $rowsToInsert[] = [
        "clientId" => Arr::random($clients)["id"],
        "name" => $this->_generateRandomName($device['osType']) . '_' . Str::random(4),
        "serialNumber" => Str::random(30),
        "osVersion" => $device['osVersion'],
        "osType" => $device['osType'],
      ];
    }
    DB::connection('distributor_2_d3tGk')->table('devices')
      ->insert($rowsToInsert);
  }

  private function _generateRandomName($osType)
  {
    if ($osType === 'ios')
      return $this->iosNames[rand(0, 70)];
    else if ($osType === 'windows')
      return $this->windowsNames[rand(0, 70)];
    if ($osType === 'android')
      return $this->androidNames[rand(0, 65)];
  }

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
        if (($responseToken instanceof Response) && $responseToken->ok()) {
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

  public function test4()
  {
    try {
      $externalConnectors = DB::connection('testing_distributor_2_d3tGk')
        ->table('configurations')
        ->whereIn('type', ['splunk', 'syslog'])
        ->get();
      foreach ($externalConnectors as $connector) {
        $configurations = json_decode($connector->configurations);
        if ($connector->type == 'splunk') {
          Http::withHeader('Authorization', 'Splunk ' . $configurations->authToken)
            ->withBody('{
    "time": 1436279439,
    "host": "localhost",
    "source": "random-data-generator",
    "sourcetype": "my_sample_data",
    "index": "main",
    "event": {
        "message": "lkasndkajsdkjasdkl happened",
        "severity": "ERROR"
    }
}
{
    "time": 1436279439,
    "host": "localhost",
    "source": "random-data-generator",
    "sourcetype": "my_sample_data",
    "index": "main",
    "event": {
        "message": "poaskkkkk happened",
        "severity": "INFO"
    }
}}')
            ->withoutVerifying()
            ->post($configurations->url);
        } else if ($connector->type == 'syslog') {
          continue;
        } else if ($connector->type == 'smtp') {
          continue;
        } else continue;
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }

  private function formatEventForSplunk($events, $configuration, $eventTypeIds = [])
  {
    if (!empty($eventTypeIds))
      $events = array_filter($events, fn($el) => in_array($el['type'], json_decode($eventTypeIds)));
    $rawString = '';
    foreach ($events as $event) {
      $rawString .= '{';

      $rawString .= '"time": ' . strtotime($event['detectedAt']) . ',';
      $rawString .= '"host": ' . 'ermetix' . ',';
      if (!empty($configuration->source))
        $rawString .= '"source": ' . $configuration->source . ',';
      if (!empty($configuration->sourcetype))
        $rawString .= '"sourcetype": ' . $configuration->sourcetype . ',';
      if (!empty($configuration->index))
        $rawString .= '"index": ' . $configuration->index . ',';

      $rawString .= '"event": {';
      $rawString .= '"message": ' . $event['description'] . ',';
      $rawString .= '"severity": "' . $this->calcCriticalityLevel($event['score']) . '",';
      $rawString .= '"deviceSerialNumber": "' . $event['serialNumber'] . '",';
      $rawString .= '"hasBeenSolved": "' . $event['hasBeenSolved'] . '",';
      $rawString .= '"remediationActionStarted": "' . $event['remediationActionStarted'] . '"';
      $rawString .= '}';
      $rawString .= '}';
    }
    return $rawString;
  }

  private function calcCriticalityLevel($score)
  {
    if ((float)$score >= 8.5) return "critic";
    else if ((float)$score >= 7.0 && (float)$score < 8.5) return "high";
    else if ((float)$score > 5.0 && (float)$score < 7.0) return "medium";
    else return "low";
  }

  public function test5()
  {
    $instance = "prova";
    $databaseName = app(DatabaseConnectionController::class)
      ->createMDM($instance);
  }

  public function test6(){
    Artisan::call("migrate --database=testing_mdm_prova_d3tGk --path=database/migrations/mdmDB --force");
  }

  public function test7()
  {
      try {
          $filters = json_decode(request()->query('filters') ?? [], true);
          $responseData = app(UemDeviceController::class)->list($filters);
          return response(["success" => true, "payload" => $responseData]);
          //return json_encode($response);
      } catch (\Exception $e) {
          CatchedExceptionHandler::handle($e);
          return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
      }
  }

    public function test8(){
        $request = request();
        try {
            $params = $request->json()->all();
            return response(app(UemDeviceController::class)->create($params));
        } catch (\Exception $e) {
            \App\Exceptions\CatchedExceptionHandler::handle($e);
            return response(["success" => false, "message" => "SERVER_ERROR"], 500);
        }
    }

}

