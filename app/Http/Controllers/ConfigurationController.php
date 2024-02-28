<?php

namespace App\Http\Controllers;

use App\Exceptions\CatchedExceptionHandler;
use App\Exceptions\ParametersException;
use App\Jobs\LogAccess;
use App\Utils\ControllerResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConfigurationController
{

  public function list($filters = []): ControllerResponse
  {
    try {
      $userAuthenticated = auth()->user();
      $query = DB::connection($userAuthenticated->nameDatabaseConnection)
        ->table('configurations');
      if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        if (empty($userAuthenticated->rolesUser->clientsFilter)) {
          $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
            ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
            ->pluck('id')
            ->toArray();
          $query = $query->where(function ($query) use ($clientList, $userAuthenticated) {
            $query = $query->where(function ($query) use ($userAuthenticated) {
              $query = $query->whereNotNull('resellerIds');
              $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->relationshipIds) . "]',`configurations`.`resellerIds`)");
            });
            $query = $query->orWhere(function ($query) use ($clientList, $userAuthenticated) {
              $query = $query->whereNotNull('clientIds');
              $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $clientList) . "]',`configurations`.`clientIds`)");
            });
          });
        } else {
          $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->clientsFilter) . "]',`configurations`.`clientIds`)");
        }
      } else if ($userAuthenticated->rolesUser->relationship == 'client') {
        $query = $query->whereNotNull('clientIds');
        $query = $query->whereRaw("JSON_CONTAINS('[" . implode(',', $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds) . "]',`configurations`.`clientIds`)");
      } else if ($userAuthenticated->rolesUser->relationship == 'distributor' && $userAuthenticated->rolesUser->clientsFilter) {
        $query = $query->whereNotNull('clientIds');
        $query = $query->whereRaw("JSON_CONTAINS('[" .
          implode(',', $userAuthenticated->rolesUser->clientsFilter)
          . "]',`configurations`.`clientIds`)");
      }

      $items = $query->get();
      foreach ($items as $item) {
        $item->configurations = json_decode($item->configurations);
        $item->clientIds = json_decode($item->clientIds);
        $item->eventTypeIds = json_decode($item->eventTypeIds);
      }
      return new ControllerResponse(true, $items);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function create($attributes): ControllerResponse
  {
    try {
      $data = [];
      $data["type"] = $attributes["type"];
      $data["userId"] = auth()->user()->id;
      $data["name"] = $attributes["name"] ?? ($attributes['type'] . '_' . time());
      if (empty($attributes['clientIds']))
        $this->setRelationshipFilterBasedOnUser($data);
      else
        $data["clientIds"] = $this->validateClientIds($attributes["clientIds"]);
      $data["eventTypeIds"] = json_encode($attributes["eventTypeIds"]);
      if ($data["type"] == "syslog" && isset($attributes["syslogConfiguration"])) {
        $data["configurations"] = json_encode($attributes["syslogConfiguration"]);
      } else if ($data["type"] == "smtp" && isset($attributes["smtpConfiguration"])) {
        $data["configurations"] = json_encode($attributes["smtpConfiguration"]);
      } else if ($data["type"] == "splunk" && isset($attributes["splunkConfiguration"])) {
        $data['configurations'] = json_encode($attributes['splunkConfiguration']);
      } else throw new ParametersException('DATA_NOT_CORRECT');
      if ($data['type'] == 'splunk') {
        $response = Http::withHeader('Authorization', 'Splunk ' . $attributes['splunkConfiguration']['authToken'])
          ->withBody($this->formatMessageForSplunk(json_decode($data['configurations'])))
          ->withoutVerifying()
          ->post($attributes['splunkConfiguration']['url']);
        if ($response->status() != 200)
          throw new \Exception('Data Not valid for configuration ' . $data["name"], 400);
      }
      DB::connection(auth()->user()->nameDatabaseConnection)->table('configurations')
        ->insert($data);
      dispatch(new LogAccess(
        from: [
          "username" => auth()->user()->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: null, type: "CREATE_CONFIGURATION", value: ["name" => $data['name'], "type" => $data['type']],
        distributorId: auth()->user()->distributor_id,
        timestamp: new \DateTime(),
        rolesUser: auth()->user()->rolesUser
      ));
      return new ControllerResponse(true, null);
    } catch (\Exception $e) {
      if ($e->getCode() == 400 || get_class($e) == 'Illuminate\Http\Client\ConnectionException')
        return new ControllerResponse(false, ["message" => "SPLUNK_CONFIGURATION_NOT_VALID"], 400);
      else {
        CatchedExceptionHandler::handle($e);
        return new ControllerResponse(false, ["message" => "SERVER_ERROR"], 500);
      }
    }
  }

  public function update($attributes): ControllerResponse
  {
    try {
      $configurationToUpdate = DB::connection(auth()->user()->nameDatabaseConnection)->table('configurations')
        ->where('id', $attributes['configurationId'])
        ->first();
      //TODO Valid request
      $data = [];
      $data["type"] = $attributes["type"];
      $data["userId"] = auth()->user()->id;
      $data["name"] = $attributes["name"] ?? ($attributes['type'] . '_' . time());
      if (empty($attributes['clientIds']))
        $this->setRelationshipFilterBasedOnUser($data);
      else
        $data["clientIds"] = $this->validateClientIds($attributes["clientIds"]);
      $data["eventTypeIds"] = json_encode($attributes["eventTypeIds"]);
      if ($data["type"] == "syslog" && isset($attributes["syslogConfiguration"])) {
        $data["configurations"] = json_encode($attributes["syslogConfiguration"]);
      } else if ($data["type"] == "smtp" && isset($attributes["smtpConfiguration"])) {
        $data["configurations"] = json_encode($attributes["smtpConfiguration"]);
      } else if ($data["type"] == "splunk" && isset($attributes["splunkConfiguration"])) {
        $data['configurations'] = json_encode($attributes['splunkConfiguration']);
      } else throw new ParametersException('DATA_NOT_CORRECT');
      if ($data['type'] == 'splunk') {
        $response = Http::withHeader('Authorization', 'Splunk ' . $attributes['splunkConfiguration']['authToken'])
          ->withBody($this->formatMessageForSplunk(json_decode($data['configurations'])))
          ->withoutVerifying()
          ->post($attributes['splunkConfiguration']['url']);
        if ($response->status() != 200)
          throw new \Exception('Data Not valid for configuration ' . $data["name"], 400);
      }
      DB::connection(auth()->user()->nameDatabaseConnection)->table('configurations')
        ->where('id', $attributes['configurationId'])
        ->update($data);

      dispatch(new LogAccess(
        from: [
          "username" => auth()->user()->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: null, type: "UPDATE_CONFIGURATION", value: ["name" => $data['name'], "type" => $data['type']], distributorId: auth()->user()->distributor_id, timestamp: new \DateTime(),
        rolesUser: auth()->user()->rolesUser
      ));
      return new ControllerResponse(true, null);
    } catch (\Exception $e) {
      if ($e->getCode() == 400 || get_class($e) == 'Illuminate\Http\Client\ConnectionException')
        return new ControllerResponse(false, ["message" => "SPLUNK_CONFIGURATION_NOT_VALID"], 400);
      else {
        CatchedExceptionHandler::handle($e);
        return new ControllerResponse(false, ["message" => "SERVER_ERROR"], 500);
      }
    }

  }

  public function delete($id)
  {
    try {
      $userAuthenticated = auth()->user();
      $configurationToDelete = DB::connection($userAuthenticated->nameDatabaseConnection)
        ->table('configurations')
        ->where('id', $id)
        ->first();
      // if user tries to delete configuration with different clientIds it will throw Exception
      //$this->validateClientIds(json_decode($configurationToDelete->clientIds));
      DB::connection(auth()->user()->nameDatabaseConnection)->table('configurations')
        ->where('id', $id)
        ->delete();
      dispatch(new LogAccess(
        from: [
          "username" => auth()->user()->username,
          "ip" => app()->request->ip(),
          "userAgent" => app()->request->userAgent()
        ],
        to: null, type: "DELETE_CONFIGURATION",
        value: ["name" => $configurationToDelete->name, "type" => $configurationToDelete->type],
        distributorId: auth()->user()->distributor_id,
        timestamp: new \DateTime(),
        rolesUser: auth()->user()->rolesUser
      ));
      return response(['success' => true]);
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return response(["success" => false, "message" => "SERVER_ERROR"]);
    }
  }

  private function applyUserRelationshipFilters(): string
  {
    $userAuthenticated = auth()->user()->load(['rolesUser']);
    if ($userAuthenticated->rolesUser->relationship != 'distributor') {
      if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        if (empty($userAuthenticated->rolesUser->clientsFilter)) {
          $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
            ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
            ->pluck('id')
            ->toArray();
        } else {
          $clientList = $userAuthenticated->rolesUser->clientsFilter;
        }
      } else if ($userAuthenticated->rolesUser->relationship == 'client') {
        $clientList = $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds;
      } else throw new \Exception('Relationship not valid');
    } else {
      $clientList = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
        ->pluck('id')
        ->toArray();
    }
    return json_encode($clientList);
  }

  private function validateClientIds($clientIds): string
  {
    $userAuthenticated = auth()->user()->load(['rolesUser']);
    if ($userAuthenticated->rolesUser->relationship != 'distributor') {
      if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        if (empty($userAuthenticated->rolesUser->clientsFilter)) {
          $clientListUserDB = DB::connection($userAuthenticated->nameDatabaseConnection)->table('clients')
            ->whereIn('resellerId', $userAuthenticated->rolesUser->relationshipIds)
            ->pluck('id')
            ->toArray();
        } else {
          $clientListUserDB = $userAuthenticated->rolesUser->clientsFilter;
        }
      } else if ($userAuthenticated->rolesUser->relationship == 'client') {
        $clientListUserDB = $userAuthenticated->rolesUser->clientsFilter ?? $userAuthenticated->rolesUser->relationshipIds;
      } else throw new \Exception('Relationship not valid');
      foreach ($clientIds as $clientIdToCheck) {
        if (!in_array($clientIdToCheck, $clientListUserDB))
          throw new \Exception('clientId not permitted');
      }
      return json_encode($clientIds);
    } else return json_encode($clientIds);
  }

  private function setRelationshipFilterBasedOnUser(&$data)
  {
    $userAuthenticated = auth()->user()->load(['rolesUser']);
    if (isset($userAuthenticated->rolesUser->clientsFilter)) {
      $clientIds = $userAuthenticated->rolesUser->clientsFilter;
      $data['clientIds'] = json_encode($clientIds);
    } else {
      if ($userAuthenticated->rolesUser->relationship == 'distributor') {
        $resellerIds = DB::connection($userAuthenticated->nameDatabaseConnection)
          ->table('resellers')
          ->pluck('id');
        $data['resellerIds'] = json_encode($resellerIds);
      } else if ($userAuthenticated->rolesUser->relationship == 'reseller') {
        $resellerIds = $userAuthenticated->rolesUser->relationshipIds;
        $data['resellerIds'] = json_encode($resellerIds);
      } else if ($userAuthenticated->rolesUser->relationship == 'client') {
        $clientIds = $userAuthenticated->rolesUser->relationshipIds;
        $data['clientIds'] = json_encode($clientIds);
      } else throw new \Exception('RELATIONSHIP_NOT_VALID');
    }
  }

  private function formatMessageForSplunk($configuration)
  {
    $rawString = '';
    $rawString .= '{';
    $rawString .= '"time": ' . time() . ',';
    $rawString .= '"host": "' . 'ermetix' . '",';
    if (!empty($configuration->source))
      $rawString .= '"source": "' . $configuration->source . '",';
    if (!empty($configuration->sourcetype))
      $rawString .= '"sourcetype": "' . $configuration->sourcetype . '",';
    if (!empty($configuration->index))
      $rawString .= '"index": "' . $configuration->index . '",';
    $rawString .= '"event": {';
    $rawString .= '"message": "' . 'CHECK_CONNECTION_ERMETIX' . '",';
    $rawString .= '}';
    $rawString .= '}';

    return $rawString;
  }

}
