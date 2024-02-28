<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\Device;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventFetcher implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $dbConnection;
  private $methodToExecute;
  private $params;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($dbConnection = '', $methodToExecute = '', $params = [])
  {
    $this->dbConnection = $dbConnection;
    $this->methodToExecute = $methodToExecute;
    $this->params = $params;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    switch ($this->methodToExecute) {
      case 'INSERT_EVENTS_INTERVAL':
        $this->_insertEventsInterval();
        break;
      default:
        break;
    }
  }

  private function _insertEventsInterval()
  {
    try {
      if(empty($this->params['events']))
        return;
      $eventsFiltered = $this->_filterEventsDuplicated($this->params['events']);
      $eventsToInsert = $eventsFiltered['eventsToInsert'];
      $clientId = $this->params['clientId'];
      //create new ones
      $result = DB::connection($this->dbConnection)->table('events')
        ->insert(
          array_map(function ($event) use ($clientId) {
            return [
              "chimpaEventId" => $event['id'],
              "clientId" => $clientId,
              "deviceSerialNumber" => $event['serialNumber'],
              "type" => $event['type'],
              "score" => $event['score'],
              "criticalityLevel" => $this->calcCriticalityLevel($event['score']),
              "detectionDate" => $event['detectionDate'],
              "updatedAt" => $event['updatedAt'],
              "description" => $event['description'],
              "docs" => $event['docs'],
              "remediationType" => $event['remediationType'],
              "remediationAction" => $event['remediationAction'],
              "remediationActionStarted" => $event['remediationActionStarted'],
              "hasBeenSolved" => $event['hasBeenSolved'],
              "subject" => $event['subject'],
            ];
          }, $eventsToInsert)
        );
      if ($result) {
        $lastDateSaved = null;
        // handle case only updated events
        $eventsToIterate = empty($eventsToInsert) ? $eventsFiltered['totalEvents'] : $eventsFiltered['eventsToInsert'];
        foreach ($eventsToIterate as $event) {
          if (is_null($lastDateSaved))
            $lastDateSaved = $event['updatedAt'];
          else {
            $oldDate = new \DateTime($lastDateSaved);
            $newDate = new \DateTime($event['updatedAt']);
            if ($newDate > $oldDate)
              $lastDateSaved = $event['updatedAt'];
          }
        }
        if(empty($lastDateSaved))
          throw new \Exception('eventsLastUpdate cant be null');

        $clientDB = Client::on($this->dbConnection)->where('id', $this->params['clientId'])->first();
        DB::connection($this->dbConnection)->transaction(function () use ($lastDateSaved, $clientDB) {
          $maxDateArray = new \DateTime($lastDateSaved);
          $maxDateStored = isset($clientDB->eventsLastUpdate) ? new \DateTime($clientDB->eventsLastUpdate) : '1997-01-02 10:54:37';
          if ($maxDateArray > $maxDateStored ) {
            $clientDB->eventsLastUpdate = $lastDateSaved;
            $clientDB->save();
          }
        });
      }
      $this->checkExternalConnectors($eventsFiltered['totalEvents'],$clientDB);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }

  }

  private function _filterEventsDuplicated($arrayToFilter)
  {
    $hashTable = [];
    $idsOfEventsToCheck = [];
    foreach ($arrayToFilter as $el) {
      if (!isset($hashTable[$el["id"]])) {
        $hashTable[$el["id"]] = $el;
        $idsOfEventsToCheck[] = $el["id"];
      } else {
        $dateStored = new \DateTime($hashTable[$el["id"]]["updatedAt"]);
        $dateToCheck = new \DateTime($el["updatedAt"]);
        if ($dateToCheck >= $dateStored)
          $hashTable[$el["id"]] = $el;
      }
    }
    $eventsBeforeFiltering = $hashTable;
    //store orphan events
    foreach ($hashTable as $element) {
      $serialNumberPresent = Device::on($this->dbConnection)->where('serialNumber', $element["serialNumber"])->first();
      $eventTypePresent = EventType::on($this->dbConnection)->where('value', $element["type"])->first();
      if (!$serialNumberPresent || !$eventTypePresent) {
        //TODO make events_not_stored_successfully model
        $eventPresentInOrphanTable = DB::connection($this->dbConnection)->table('events_not_stored_successfully')
          ->where("chimpaEventId", $element["id"] )
          ->where('clientId', $this->params["clientId"])
          ->first();
        if(!$eventPresentInOrphanTable){
          DB::connection($this->dbConnection)->table('events_not_stored_successfully')->insert([
            "chimpaEventId" => $element['id'],
            "clientId" => $this->params['clientId'],
            "deviceSerialNumber" => $element['serialNumber'],
            "type" => $element['type'],
            "score" => $element['score'],
            "criticalityLevel" => $this->calcCriticalityLevel($element['score']),
            "detectionDate" => $element['detectionDate'],
            "updatedAt" => $element['updatedAt'],
            "description" => $element['description'],
            "docs" => $element['docs'],
            "remediationType" => $element['remediationType'],
            "remediationAction" => $element['remediationAction'],
            "remediationActionStarted" => $element['remediationActionStarted'],
            "hasBeenSolved" => $element['hasBeenSolved'],
            "subject" => $element['subject'],
          ]);
        }
        else{
          DB::connection($this->dbConnection)->table('events_not_stored_successfully')
            ->where('chimpaEventId',$element["id"] )
            ->where('clientId', $this->params["clientId"])
            ->update(
            [
              "hasBeenSolved" => $element["hasBeenSolved"],
              "remediationActionStarted" => $element['remediationActionStarted'],
              "updatedAt" => $element['updatedAt'],
              "description" => $element['description'],
              "docs" => $element['docs']
            ]
          );
        }
        unset($hashTable[$element["id"]]);
      }
    }
    //Update events already present
    $eventsToUpdate = Event::on($this->dbConnection)
      ->whereIn("chimpaEventId", $idsOfEventsToCheck)
      ->where('clientId', $this->params["clientId"])
      ->get();
    foreach ($eventsToUpdate as $eventToUpdate) {
      $eventWithNewInfo = $hashTable[$eventToUpdate["chimpaEventId"]];
      $eventToUpdate->update(
        [
          "hasBeenSolved" => $eventWithNewInfo["hasBeenSolved"],
          "remediationActionStarted" => $eventWithNewInfo['remediationActionStarted'],
          "updatedAt" => $eventWithNewInfo['updatedAt'],
          "description" => $eventWithNewInfo['description'],
          "docs" => $eventWithNewInfo['docs']
        ]
      );
      unset($hashTable[$eventToUpdate["chimpaEventId"]]);
    }

    return ["eventsToInsert" => $hashTable , "totalEvents" => $eventsBeforeFiltering];
  }

  private function calcCriticalityLevel($score)
  {
    if ((float)$score >= 8.5) return "critic";
    else if ((float)$score >= 7.0 && (float)$score < 8.5) return "high";
    else if ((float)$score > 5.0 && (float)$score < 7.0) return "medium";
    else return "low";
  }

  private function checkExternalConnectors($events,$clientInfo){
    $externalConnectors = DB::connection($this->dbConnection)
      ->table('configurations')
      ->whereIn('type', ['splunk', 'syslog'])
      ->where(function($query) {
        $query->whereJsonContains('clientIds',$this->params['clientId']);
        $query->orWhereJsonContains('resellerIds',$this->params['resellerId']);
      })
      ->get();

    foreach ($externalConnectors as $connector) {
      try {
        $configurations = json_decode($connector->configurations);
        $eventTypeIds = !empty($connector->eventTypeIds) ? json_decode($connector->eventTypeIds) : [];
        if ($connector->type == 'splunk') {
          $body = $this->formatEventForSplunk($events, $configurations, $eventTypeIds, $clientInfo);
          //CASE EVENT TYPE FILTERS RETURN AN EMPTY ARRAY,SO IT IS USELESS TO SEND TO SPLUNK AN EMPTY CALL
          if($body == '')
            continue;
          $response = Http::withHeader('Authorization', 'Splunk ' . $configurations->authToken)
            ->withBody($body)
            ->withoutVerifying()
            ->post($configurations->url);
          if($response->status() != 200)
            Throw new \Exception('Data Not saved for configuration ' . $connector->name. 'with ' .$body);
        } else if ($connector->type == 'syslog') {
          continue;
        } else if ($connector->type == 'smtp') {
          continue;
        } else continue;
      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
        continue;
      }
    }
  }

  private function formatEventForSplunk($events,$configuration,$eventTypeIds = [],$clientDB)
  {

    if(!empty($eventTypeIds)){
      $eventsFiltered = array_filter($events, fn($el) => in_array($el['type'],$eventTypeIds));
    }
    else $eventsFiltered = $events;

    $rawString = '';
    foreach ($eventsFiltered as $event){
      $rawString .= '{';

      $rawString .= '"time": '. strtotime($event['detectionDate']) . ',';
      $rawString .= '"host": "'. 'ermetix' . '",';
      if(!empty($configuration->source))
        $rawString .= '"source": "'. $configuration->source . '",';
      if(!empty($configuration->sourcetype))
        $rawString .= '"sourcetype": "'. $configuration->sourcetype . '",';
      if(!empty($configuration->index))
        $rawString .= '"index": "'. $configuration->index . '",';
      $rawString .= '"event": {';
      $rawString .= '"id": "'. $event['id'] . '",';
      $rawString .= '"message": "'. str_replace('"','',$event['description'] ). '",';
      $rawString .= '"severity": "'. $this->calcCriticalityLevel($event['score']) . '",';
      $rawString .= '"deviceSerialNumber": "'. $event['serialNumber'] . '",';
      $rawString .= '"hasBeenSolved": "'. $event['hasBeenSolved'] . '",';
      $rawString .= '"client": "'. ($clientDB->companyName ?? 'N/A') . '",';
      $rawString .= '"clientEmail": "'. ($clientDB->email ?? 'N/A') . '",';
      $rawString .= isset($event['subject']) ? ('"subject": "'. $this->calcSubjectString($event['subject']) . '",') : '';
      $rawString .= '"remediationActionStarted": "'. $event['remediationActionStarted'] .'"';
      $rawString .= '}';
      $rawString .= '}';
    }
    return $rawString;
  }

  private function calcSubjectString(string $eventSubject): string{
    if ($this->isJson($eventSubject)) {
      $array = get_object_vars(json_decode($eventSubject));
      return reset($array);
    } else {
      return $eventSubject;
    }
  }

  private function isJson($string): bool {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
  }

}
