<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class EventController extends CrudController
{
  public function __construct()
  {
    $this->setModel('App\Models\Event');
  }

  /**
   * @Override
   * Display a listing of the resource with filters.
   *
   */
  public function list($filters = [])
  {
    $rows = [];
    $countScoreInterval = [];
    try {
      $check = $this->_setDbConnectionFromAuthUser();
      $dataToReturn = [];
      if (!$check)
        return 'error';
      $userAuthenticated = auth()->user()->load(['rolesUser']);
      $query = $this->_getModel()::on($this->_getDbConnection());

      //FILTER CASES-----------------------------------------------------------------------------------------------------------------------------------
      if (isset($filters["clientIds"]) || (!empty($userAuthenticated->rolesUser->clientsFilter)) || $userAuthenticated->rolesUser->relationship != 'distributor') {
        if($userAuthenticated->rolesUser->relationship == 'reseller'){
          $clientList = DB::connection($this->_getDbConnection())->table('clients')
            ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
            ->pluck('id')
            ->toArray();
        }
        else if($userAuthenticated->rolesUser->relationship == 'client'){
          $clientList = $userAuthenticated->rolesUser->relationshipIds;
        }
        else $clientList = [];
        //check valid filters are valid
        if (!empty($clientList) && !empty($filters["clientIds"]))
          $query = $query->whereIn('events.clientId', array_filter($filters["clientIds"], fn($el) => in_array($el, $clientList)));
        //no filters set, impose roles user ones
        else if (!empty($clientList) && empty($filters["clientIds"]))
          $query = $query->whereIn("events.clientId", $clientList);
        //no roles, only filters set by user and user related to distributor
        else if (!empty($filters["clientIds"]))
          $query = $query->whereIn("events.clientId", $filters["clientIds"]);
      }

      if (isset($filters["period"])) {
        $from = new \Datetime($filters['period']['from']);
        $to = new \Datetime($filters['period']['to']);
        if(empty($filters['period']['considerAlsoUpdate']))
          $query = $query->whereBetween('events.detectionDate', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
        else{
          $query = $query->where(function ($query) use ($from,$to) {
            $query = $query->whereBetween('events.detectionDate', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
            $query = $query->orWhereBetween('events.updatedAt', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
          });
        }
      }

      if (isset($filters["eventTypes"]) || (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->eventTypeFilter))) {
        $query = $query->join('event_types', 'events.type', '=', 'event_types.value');
        //check valid filters are valid
        if (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->eventTypeFilter) && isset($filters["eventTypes"]) && count($filters["eventTypes"]) > 0) {
          if (!$filters["eventTypes"]["include"])
            $query = $query
              ->whereNotIn('event_types.value', array_filter($filters["eventTypes"]["items"], fn($el) => in_array($el, $userAuthenticated->rolesUser->eventTypeFilter)))
              ->whereIn('event_types.value', $userAuthenticated->rolesUser->eventTypeFilter);
          else
            $query = $query->whereIn('event_types.value', array_filter($filters["eventTypes"]["items"], fn($el) => in_array($el, $userAuthenticated->rolesUser->eventTypeFilter)));
        } //no filters set, impose roles user ones
        else if (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->eventTypeFilter))
          $query = $query->whereIn('event_types.value', $userAuthenticated->rolesUser->eventTypeFilter);
        //no roles, only filters set by user
        else if (isset($filters["eventTypes"])) {
          if (!$filters["eventTypes"]["include"])
            $query = $query->whereNotIn('event_types.value', $filters["eventTypes"]["items"]);
          else
            $query = $query->whereIn('event_types.value', $filters["eventTypes"]["items"]);
        }
      }

      if (isset($filters["serialNumbers"])) {
        if (!$filters["serialNumbers"]["include"])
          $query = $query->whereNotIn('events.deviceSerialNumber', $filters["serialNumbers"]["items"]);
        else
          $query = $query->whereIn('events.deviceSerialNumber', $filters["serialNumbers"]["items"]);
      }

      if (isset($filters["selectedDays"])) {
        $query = $query->where(function ($query) use ($filters) {
          $firstFilterSet = false;
          $timezone = $filters["timezone"] ?? 'Europe/London';
          foreach ($filters["selectedDays"] as $selectedDay) {
            $from = Carbon::createFromFormat('Y-m-d H:i:s', $selectedDay . '00:00:00', $timezone);
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $selectedDay . '23:59:59', $timezone);
            $from->setTimezone('UTC');
            $to->setTimezone('UTC');
            if (!$firstFilterSet) {
              $query = $query->whereBetween('events.detectionDate', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
              $firstFilterSet = true;
            } else
              $query = $query->orwhereBetween('events.detectionDate', [$from->format('Y-m-d H:i:s'),$to->format('Y-m-d H:i:s')]);
          }
        });
      }

      if (isset($filters["criticality"]) || (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->scoreFilter))) {

        //check valid filters are valid
        if (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->scoreFilter) && !empty($filters["criticality"]))
          $criticalityFilters = array_filter($filters["criticality"], fn($el) => in_array($el, $userAuthenticated->rolesUser->scoreFilter));
        //no filters set, impose roles user ones
        else if (isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->scoreFilter))
          $criticalityFilters = $userAuthenticated->rolesUser->scoreFilter;
        //no roles, only filters set by user
        else if (isset($filters["criticality"]))
          $criticalityFilters = $filters["criticality"];

        $query = $query->where(function ($query) use ($criticalityFilters) {
          $firstFilterSet = false;
          if (in_array('critic', $criticalityFilters)) {
            $query = $query->where('criticalityLevel', 'critic');
            $firstFilterSet = true;
          }
          if (in_array('high', $criticalityFilters)) {
            if (!$firstFilterSet) {
              $query = $query->where('criticalityLevel', 'high');
              $firstFilterSet = true;
            } else
              $query = $query->orWhere('criticalityLevel', 'high');
          }
          if (in_array('medium', $criticalityFilters)) {
            if (!$firstFilterSet) {
              $query = $query->where('criticalityLevel', 'medium');
              $firstFilterSet = true;
            } else
              $query = $query->orWhere('criticalityLevel', 'medium');
          }
          if (in_array('low', $criticalityFilters)) {
            if (!$firstFilterSet)
              $query = $query->where('criticalityLevel', 'low');
            else
              $query = $query->orWhere('criticalityLevel', 'low');
          }
        });
      }

      if (isset($filters["countScoreInterval"])) {

        $query = $query->selectRaw("count(*) as num, criticalityLevel")
          ->groupBy('criticalityLevel');
        $queryScores = $query->get()->toArray();
        foreach ($queryScores as $el) {
          $countScoreInterval[$el['criticalityLevel']] = $el['num'];
        }
      }

      //RELATIONSHIPS CASES ----------------------------------------------------------------------------------------------------------------------
      if (isset($filters["withDevice"])) {
        $query = $query->join('devices', 'devices.serialNumber', '=', 'events.deviceSerialNumber');
        if (isset($filters["deviceOsType"])) {
          $query = $query->where('devices.osType', $filters['deviceOsType']);
        }
        if (isset($filters["deviceOsVersion"])) {
          $query = $query->where('devices.osVersion', $filters['deviceOsVersion']);
        }
      }

      if (isset($filters["withClient"])) {
        $query = $query->with('client');
      }

      if (isset($filters["withEventType"])) {
        $query = $query->join('event_types', 'event_types.value', '=', 'events.type');
      }

      //TYPE OF RETURN CASES ----------------------------------------------------------------------------------------------------------------

      //EVENTS_CHART CASE ----------------------------------------------------------------------------------------------------------------
      if (isset($filters["groupBy"]) && $filters["groupBy"][0] === "eventsPerDayPerCriticality") {
        $timezone = $filters["timezone"] ?? 'Europe/London';

        $dateTimeZone = new DateTimeZone($timezone);

        // GET THE OFFSET IN SECONDS FROM UTC
        $offsetSeconds = $dateTimeZone->getOffset(new DateTime('now'));

        //CONVERT THE OFFSET FROM SECONDS TO THE HH:MM FORMAT.
        $offsetHours = floor($offsetSeconds / 3600);
        $offsetMinutes = floor(($offsetSeconds % 3600) / 60);

        //BUILD THE OFFSET STRING IN THE FORMAT +/-HH:MM.
        $offsetString = sprintf('%+03d:%02d', $offsetHours, $offsetMinutes);
        $query = $query->selectRaw("DATE(CONVERT_TZ(detectionDate,'+00:00', ?)) as date, COUNT(*) as num, criticalityLevel",[$offsetString])
          ->groupBy('criticalityLevel')
          ->groupBy('date')
          ->orderBy('date');
        $rowsToFilter = $query->get();
        /*structure result
          array=[
            date => "2023-01-24"
            criticalityLevel => "low"
            num => {int} 1
          ]
        */
        foreach ($rowsToFilter as $row) {
          if (isset($rows[$row->date]))
            $rows[$row->date][$row->criticalityLevel] = $row->num;
          else {
            $rows[$row->date] = [];
            $rows[$row->date][$row->criticalityLevel] = $row->num;
          }
        }
      }
      //SUNBURST CASE --------------------------------------------------------------------------------------------------------------------------------
      else if (isset($filters["groupBy"]) && $filters['groupBy'][0] === 'eventsPerSunburstChart') {
        //join with devices implicit, when implement api add control join devices
        $query = $query->selectRaw("osType, osVersion, COUNT(*) as num")
          ->groupBy('osType')
          ->groupBy('osVersion');
        $rowsToFilter = $query->get();
        $rows['android'] = [];
        $rows['ios'] = [];
        $rows['windows'] = [];
        $rows['osx'] = [];
        foreach ($rowsToFilter as $row) {
          $rows[$row->osType][] = ['version' => $row->osVersion, 'num' => $row->num];
        }
      }
      //PAGINATE CASE ----------------------------------------------------------------------------------------------------------------
      else if (isset($filters["paginate"])) {
        if (isset($filters["orderBy"])) {
          $query = $query->join('clients', 'events.clientId', '=', 'clients.id');
          foreach ($filters["orderBy"] as $orderByFilter) {
            $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
          }
        }
        $rows = $query->paginate(array_key_exists("rowsPerPage", $filters) ? (int)$filters["rowsPerPage"] : 15,
          ['events.*', 'devices.osType', 'devices.osVersion', 'devices.name', 'devices.serialNumber', 'clients.companyName'], 'page', $filters["page"]);
      }
      //COUNT CASE ----------------------------------------------------------------------------------------------------------------
      else if (isset($filters["onlyNumericData"])) {
        if (isset($filters["orderBy"])) {
          foreach ($filters["orderBy"] as $orderByFilter) {
            $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
          }
        }
        if (isset($filters["groupBy"])) {
          foreach ($filters["groupBy"] as $groupByFilter) {
            $query = $query->groupBy($groupByFilter);
          }
        }
        if (isset($filters["sameQueryLastPeriod"])) {
          $filters["period"] = $filters["sameQueryLastPeriod"];
          unset($filters["sameQueryLastPeriod"]);
          $dataToReturn["sameQueryLastPeriod"] = $this->list($filters);
        }
        if (isset($filters["selectAttributes"]))
          $query = $query->selectRaw('count(*) as num, '. join(',', $filters["selectAttributes"]));
        else
          $query = $query->selectRaw('count(*) as num');

        $rows = $query->get();
      } //GENERIC CASE ----------------------------------------------------------------------------------------------------------------
      else {
        if (isset($filters["eventTypesIncluded"])) {
          $queryForEventType = clone $query;
          if (!(isset($userAuthenticated->rolesUser) && isset($userAuthenticated->rolesUser->eventTypeFilter)))
            $queryForEventType = $queryForEventType->join('event_types', 'events.type', '=', 'event_types.value');
          //TODO add check roles if request is malicious
          $queryForEventType = $queryForEventType->selectRaw('event_types.value, event_types.key , count(*) as num');
          $queryForEventType = $queryForEventType->groupBy('event_types.value');
          $dataToReturn["eventTypesIncluded"] = $queryForEventType->get();
        }

        if (isset($filters["orderBy"])) {
          foreach ($filters["orderBy"] as $orderByFilter) {
            $query = $query->orderBy($orderByFilter['attribute'], $orderByFilter['order']);
          }
        }
        if (isset($filters["selectAttributes"]))
          $query = $query->selectRaw(join(',',$filters["selectAttributes"]));
        else
          $query = $query->select('events.*', 'devices.osType', 'devices.osVersion', 'devices.name', 'devices.serialNumber');//TODO recheck

        if (isset($filters["groupBy"])) {
          foreach ($filters["groupBy"] as $groupByFilter) {
            $query = $query->groupBy($groupByFilter);
          }
        }
        if (isset($filters["queryForExport"])) {
          if (empty($filters["eventTypes"]))
            $query = $query->join('event_types', 'events.type', '=', 'event_types.value');
          return $query;
        }
        $rows = $query->get();
      }

      $dataToReturn["rows"] = $rows;
      $dataToReturn["countScoreInterval"] = $countScoreInterval;

      return $dataToReturn;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR', 500]);
    }
  }

  public function updateEvent($id, $dataToUpdate)
  {
    try {
      $check = $this->_setDbConnectionFromAuthUser();
      $eventToUpdate = $this->_getModel()::on($this->_getDbConnection())->find($id);
      foreach ($dataToUpdate as $key => $value) {
        $eventToUpdate->$key = $value;
      }
      $eventToUpdate->save();
      return $eventToUpdate;

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR', 500]);

    }
  }

  public function resolveEvent($id)
  {
    try {
      if(!is_int($id) && $id <= 0)
        throw new Exception('ID_NOT_VALID');
      //check if Event exists with the right user
      if (!$this->_setDbConnectionFromAuthUser())
        return response(['success' => false, 'message' => 'SERVER_ERROR'], 500); // todo Error Handler Database not found, critic error alert via mail
      $eventToResolve = Event::on($this->_getDbConnection())->where('id', $id)->first();
      if (is_null($eventToResolve))
        return response(['success' => false, 'message' => 'EVENT_NOT_FOUND'], 406);
      $clientCredentials = Client::on($this->_getDbConnection())->where('id', $eventToResolve->clientId)->first();
      /**
       * Call to api to get access_token
       * @return array $response
       * $response = [
       *     "access_token" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841"
       *     "expires_in" => 3600
       *     "token_type" => "bearer"
       *     "scope" => "data.write"
       *     "refresh_token" => "bbe3fc84dd09b3832b79c6231539a4d8ddc0bfe9"
       * ]
       */
      $responseAccessToken = Http::asForm()->post('https://'
        . $clientCredentials->host . '/' . $clientCredentials->baseUrl . '/api/latest/mssp/authorize', [
        'grant_type' => 'client_credentials',
        'client_id' => config('app.client_id'),
        'client_secret' => config('app.client_secret'),
        'scope' => 'data.write',
      ]);

      if ($responseAccessToken->status() == 200) {
        $bodyResponse = $responseAccessToken->json();
        $responseCommand = Http::withToken($bodyResponse['access_token'])
          ->post('https://'
            . $clientCredentials->host . '/' . $clientCredentials->baseUrl . '/api/latest/mssp/remediate_event', [
            'eventId' => $eventToResolve->chimpaEventId
          ]);
        $responseStatus = $responseCommand->status();
        if ($responseStatus == 200){
          $eventToResolve->remediationActionStarted = true;
          $eventToResolve->save();
          return response(['success' => true], 200);
        }
        else if ($responseStatus == 400)
          return response(['success' => false, 'message' => 'INVALID_ID'], 400);
        else if ($responseStatus == 404)
          return response(['success' => false, 'message' => 'EVENT_NOT_FOUND'], 404);
        else if ($responseStatus == 406)
          return response(['success' => false, 'message' => 'EVENT_NOT_FOUND'], 406);
        else
          return response(['success' => false, 'message' => 'SERVER_ERROR_CHIMPA'], 500);

      } else {
        return response(['success' => false, 'message' => 'SERVER_ERROR_CHIMPA'], 500);
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'SERVER_ERROR'], 500);
    }
  }

}
