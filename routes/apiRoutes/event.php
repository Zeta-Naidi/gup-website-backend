<?php

use App\Exceptions\CatchedExceptionHandler;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

Route::group(['prefix' => 'event', 'middleware' => ['auth:sanctum', 'onlySpaAuthenticateUsers', 'blockedHosts','siemUser']], function () {
  $validFieldsListEvents = [
    'clientIds' => 'array|min:1',
    'clientIds.*' => 'required|integer|min:1|max:10000',
    'criticality' => 'nullable|array',
    'criticality.*' => Rule::in(['critic', 'high', 'medium', 'low']),
    'countScoreInterval' => 'nullable|boolean',
    'deviceOsType' => 'nullable|string|in:ios,android,windows,osx',
    'deviceOsVersion' => 'nullable|string', //TODO missing control on device version regex
    'eventTypes' => 'nullable|array',
    'eventTypes.items' => 'array',
    'eventTypes.items.*' => 'required|integer|min:0|max:100',
    'eventTypes.include' => 'boolean',
    'eventTypesIncluded' => 'nullable|boolean',
    'groupBy' => 'nullable|array',
    'groupBy.*' => Rule::in([
      'eventsPerDayPerCriticality',
      'eventsPerSunburstChart',
      'serialNumber',
      'criticalityLevel',
      'deviceSerialNumber',
      'detectionDate',
      'type',
      'osType'
    ]),//TODO ADD
    'orderBy' => 'nullable|array',
    'orderBy.*.attribute' => Rule::in(['name', 'detectionDate', 'score', 'companyName', 'devices.name']),
    'orderBy.*.order' => Rule::in(['desc', 'asc']),
    'onlyNumericData' => 'nullable|boolean',
    'paginate' => 'nullable|boolean',
    'page' => 'required_if:paginate,true|nullable|integer|min:1',
    'period' => 'required|array',// MUST BE PRESENT PERIOD
    'period.from' => 'required|date_format:Y-m-d H:i:s',
    'period.to' => 'required|date_format:Y-m-d H:i:s',
    'period.considerAlsoUpdate' => 'nullable|boolean',
    'queryForExport' => 'nullable|boolean',
    'rowsPerPage' => 'nullable|integer|min:10|max:150',
    'sameQueryLastPeriod' => 'nullable|array',
    'sameQueryLastPeriod.from' => 'date',//'date_format:Y-m-d\TH:i:sP',//todo ADD TYPE
    'sameQueryLastPeriod.to' => 'date',//'date_format:Y-m-d\TH:i:sP',//todo ADD TYPE
    'selectAttributes' => 'nullable|array',
    'selectAttributes.*' => Rule::in([
      'eventsPerSunburstChart',
      'serialNumber',
      'name',
      'osType',
      'osVersion',
      'criticalityLevel',
      'deviceSerialNumber',
      'count(*)',
      'type',
      'DATE(detectionDate) as date',
      'event_types.key',
      'event_types.value',
      'devices.osType as deviceOsType',
      'detectionDate',
      'hasBeenSolved',
      'score',
      'description',
      'remediationActionStarted',
      'events.id',
      'events.*'
    ]),
    'selectedDays' => 'nullable|array',
    'selectedDays.*' => 'string|date_format:Y-m-d',
    'serialNumbers' => 'nullable|array',
    'serialNumbers.items' => 'array',
    'serialNumbers.items.*' => 'required|string|max:100|min:4', // TODO regex serialNumber
    'serialNumbers.include' => 'boolean',
    'timezone' => [
      'string',
      'max:40',
      'regex:/^[A-Za-z0-9\/_-]+$/'
    ],
    'withClient' => 'nullable|boolean',
    'withDevice' => 'nullable|boolean',
    'withEventType' => 'nullable|boolean'
  ];
  Route::get('export', function (Request $request) use ($validFieldsListEvents) {
    try {
      $filters = $request->query('filters');
      $filters = json_decode($filters, true);
      $filters = Validator::make($filters, [
        ...$validFieldsListEvents,
        'modExport' => 'required|string|in:CSV,SYSLOG_RAW,SYSLOG_JSON,MISP'
      ])->validate();
      $filters = [
        ...$filters,
        "withDevice" => $filters['withDevice'] ?? true,
        "queryForExport" => true,
        "selectAttributes" => [
          "events.id",
          "deviceSerialNumber",
          "event_types.key",
          "score",
          "criticalityLevel",
          "detectionDate",
          "description",
          "hasBeenSolved",
          "remediationActionStarted",
          "devices.osType",
          "devices.osVersion",
          "devices.name", //NBkiJGWgRvcdu5Ojbcb4XTy0mR7e1JGVFzdMDEkw
          "events.type",
          "events.subject"
        ]
      ];
      $events = app(EventController::class)->list($filters)->get();

      if ($filters['modExport'] == 'CSV') {
        $headers = array(
          "Content-type" => "text/csv",
          "Content-Disposition" => "attachment; filename=file.csv",
          "Pragma" => "no-cache",
          "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
          "Expires" => "0"
        );
        $columns = ["Serial Number", "Notification Type", "Score", "Criticality Level", "Detection Date", "Description"];
        $callback = function () use ($events, $columns) {
          $file = fopen('php://output', 'w');
          fputcsv($file, $columns);
          foreach ($events as $event) {
            fputcsv($file, array(
              $event->deviceSerialNumber,
              $event->key,
              $event->score,
              $event->criticalityLevel,
              $event->detectionDate,
              $event->description,
            ));
          }
          fclose($file);
        };
        return response()->stream($callback, 200, $headers);
      } else if ($filters['modExport'] == 'SYSLOG_RAW' || $filters['modExport'] == 'SYSLOG_JSON') {
        if ($filters['modExport'] == 'SYSLOG_RAW') {
          $syslogData = '';
          foreach ($events as $event) {
            $syslogData .=
              '<' . $event->score * 10 . '>' . ' ' .
              "$event->detectionDate ermetix.com ERMETIX - $event->key" . "\n" .
              "[ deviceSerialNumber=" . '"' . "$event->deviceSerialNumber" . '"' . " description= " . '"' . "$event->description" . '"' . ']' . "\r\n";
          }
        } //SYSLOG_JSON CASE
        else {
          $syslogData = [];
          foreach ($events as $event) {
            $syslogData [] =
              '<' . $event->score * 10 . '>' . ' ' .
              "$event->detectionDate ermetix.com ERMETIX - $event->key" . "\n" .
              "[ deviceSerialNumber=" . '"' . "$event->deviceSerialNumber" . '"' . " description= " . '"' . "$event->description" . '"' . ']' . "\r\n";
          }
        }
        return response(['success' => true, 'payload' => $syslogData]);
      } else if ($filters['modExport'] == 'MISP') {
        $eventsToBeReturned = [];
        $criticalityMapper = ['low' => 1, 'medium' => 2, 'high' => 3, 'critic' => 4];
        foreach ($events as $event) {
          $mispObjects = \App\Security\MispFormatter::formatAttributesAndDeviceObject($event);
          $eventsToBeReturned [] = [
            "id" => (string)$event->id,
            "distribution" => "0",
            "info" => $event->description,
            "date" => substr($event->detectionDate,0,10),
            "published" => false,
            "analysis" => $event->hasBeenSolved ? '2' : ($event->remediationActionStarted ? '1' : '0'),
            "timestamp" => strtotime($event->detectionDate),
            "sharing_group_id" => "1",
            "proposal_email_lock" => false,
            "locked" => false,
            "threat_level_id" => $criticalityMapper[$event->criticalityLevel] ?? 4,
            "disable_correlation" => true,
            "Object" => !empty($mispObjects["device"]) ? $mispObjects["device"] : [],
            "Attribute" => !empty($mispObjects["attribute"]) ? $mispObjects["attribute"] : [],
          ];

          //"publish_timestamp" => "1617875568",
          //"sighting_timestamp" => "1617875568",
          //"org_id" => "12345",
          //"orgc_id" => "12345",
          //"uuid" => "c99506a6-1255-4b71-afa5-7b8ba48c3b1b",
          //"attribute_count" => "321",
          //"extends_uuid" => "c99506a6-1255-4b71-afa5-7b8ba48c3b1b",
          //"event_creator_email" => "user@example.com"
        }
        return response(['success' => true, 'payload' => $eventsToBeReturned]);
      }
    }catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  });
  Route::get('', function (Request $request) use ($validFieldsListEvents) {
    try {
      $filters = json_decode($request->query('filters') ?? [], true);
      $filters = Validator::make($filters, $validFieldsListEvents)->validate();
      $responseData = app(\App\Http\Controllers\EventController::class)->list($filters);
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
  /*  Route::post('', function (Request $request) {
      try {
        $params = $request->post();
        (new Controllers\EventController())->create($params);
      } catch (\Exception $e) {
        CatchedExceptionHandler::handle($e);
        return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
      }
    });*/
  //Route::get('{id}', [\App\Http\Controllers\EventController::class, 'get']);
  Route::put('{id}', function ($id, Request $request) {
    try {
      if (!is_int($id) && $id <= 0)
        throw new Exception('ID_NOT_VALID');
      $params = $request->validate([
        'hasBeenSolved' => 'boolean'
      ]);
      $responseData = app(\App\Http\Controllers\EventController::class)->updateEvent($id, $params);
      return response(['success' => true, 'payload' => $responseData]);
    } catch(ValidationException $e){
      return response(['success'=> false,'message'=>'BAD_REQUEST'],400);
    }catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  });
  //Route::delete('{id}', [\App\Http\Controllers\EventController::class, 'delete']);
  Route::post('resolve/{id}', [\App\Http\Controllers\EventController::class, 'resolveEvent']);

});
