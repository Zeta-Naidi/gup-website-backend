<?php

namespace App\Http\Controllers;

use App\Dtos\Controller\ControllerResponse;
use App\Entities\UemDeviceEntity;
use App\Jobs\FetchIconApp;
use App\Models\Client;
use App\Models\Event;
use App\Models\UemDevice;
use App\Models\UemDeviceDetails;
use App\Repositories\TagRepository;
use App\Repositories\UemDeviceRepository;
use App\Security\SecurityPostureDeviceHandler;
use GuzzleHttp\Promise\PromiseInterface;
use Hamcrest\Type\IsString;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UemDeviceController extends CrudController
{
  public function __construct()
  {
    $this->_setDbConnectionFromAuthUser();
    $this->setModel('App\Models\UemDevice');
  }

  public function list($parameters = [])
  {
    try {
      $pagination = [];
      $filters = [];

      if(isset($parameters['rowsPerPage']) && isset($parameters['page'])){
        $pagination= [
          "rowsPerPage" => $parameters['rowsPerPage'],
          "page" => $parameters['page'],
        ];
      }

      if(isset($parameters['serialOrName'])){
        $filters= ["serialOrName" => $parameters['serialOrName']];
      }

      if(isset($parameters['status'])){
        $filters= ["status" => $parameters['status']];
      }
 /*     // TODO TO IMPLEMENT
      if (isset($filters["osType"]) && !empty($filters["osType"])) {
        $osTypes = $filters["osType"];
        $query = $query->whereIn('osType', $osTypes);
      }

      // ENROLLMENT TYPE CASE
      if (isset($filters["enrollmentType"]) && !empty($filters["enrollmentType"])) {
        $enrollmentTypes = $filters["enrollmentType"];
        $query = $query->whereIn(
          'enrollmentType',
          $enrollmentTypes
        );
      }

      //PAGINATE CASE
      if (isset($filters["paginate"]) && $filters["paginate"]) {
        $rowsPerPage = (int)$filters["rowsPerPage"] ?? 5;
        $currentPage = (int)$filters["page"] ?? 1;
        return $query->paginate($rowsPerPage, ['*'], 'page', $currentPage);
      }*/
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listPaginated = $repository->list(pagination: $pagination, filters: $filters);
      return $listPaginated->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }

  }

  public function getDeviceById($id): ControllerResponse
  {
    try {
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listItem = $repository->getDeviceById(id: $id);
      return $listItem->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function getDeviceDetailsById($id): ControllerResponse
  {
    try {
      /**
      * @var UemDeviceRepository $repository
      */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listPaginated = $repository->getDeviceDetailsById(id: $id);
      return $listPaginated->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  // copy
//  public function getDeviceDetailsById($id)
//  {
//    try {
//      $deviceDetails = UemDeviceDetails::on('testing_mdm_prova_d3tGk')->find($id);
//      $decodedDetails = $deviceDetails->getDecodedAttributes();
//      return $decodedDetails;
//    } catch (\Exception $e) {
//      \App\Exceptions\CatchedExceptionHandler::handle($e);
//      return ['success' => false, 'message' => 'Error fetching device details'];
//    }
//  }

  public function create($params = []): ControllerResponse
  {
    try {
      $newDevice = new UemDeviceEntity($params);
      /**
       * @var UemDeviceRepository $repository
       */
      $repository = app(UemDeviceRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      return $repository->store($newDevice)->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

}

/*public function getDeviceDetailsById($id)
{
  try {
    $deviceDetails = UemDeviceDetails::on('testing_mdm_prova_d3tGk')->find($id);
    $decodedDetails = $deviceDetails->getDecodedAttributes();
    return $decodedDetails;
  } catch (\Exception $e) {
    \App\Exceptions\CatchedExceptionHandler::handle($e);
    return ['success' => false, 'message' => 'Error fetching device details'];
  }
}

public function create($request = [])
{
  try {
    $device = new UemDevice();
    $device = $device->on('testing_mdm_prova_d3tGk');
    $deviceDetails = new UemDeviceDetails();
    $deviceDetails = $deviceDetails->on('testing_mdm_prova_d3tGk');

    $deviceCreated = $device->create([
      'parentDeviceId' => $request['parentDeviceId'] ?? null,
      'deviceName' => $request['deviceName'] ?? null,
      'enrollmentType' => $request['enrollmentType'] ?? null,
      'modelName' => $request['modelName'] ?? null,
      'macAddress' => $request['macAddress'] ?? null,
      'meid' => $request['meid'] ?? null,
      'osType' => $request['osType'],
      'osEdition' => $request['osEdition'],
      'osVersion' => $request['osVersion'],
      'udid' => $request['udid'] ?? null,
      'vendorId' => $request['vendorId'] ?? null,
      'osArchitecture' => $request['osArchitecture'] ?? null,
      'abbinationCode' => $request['abbinationCode'] ?? null,
      'mdmDeviceId' => $request['mdmDeviceId'] ?? null,
      'serialNumber' => $request['serialNumber'] ?? null,
      'imei' => $request['imei'] ?? null,
      'isDeleted' => $request['isDeleted'] ?? 0,
      'phoneNumber' => $request['phoneNumber'] ?? null,
      'isOnline' => $request['isOnline'] ?? 0,
      'brand' => $request['brand'] ?? null,
      "configuration" => json_encode($request['configuration'] ?? null),
      "deviceIdentity" => json_encode($request['deviceIdentity'] ?? null),
    ]);
    $details = $request['deviceDetails'] ?? null;
    $deviceDetailsCreated = $deviceDetails->create([
      'deviceId' => $device->id,
      'hardwareDetails' => json_encode($details['hardwareDetails'] ?? null),
      'technicalDetails' => json_encode($details['technicalDetails'] ?? null),
      'permissionsAndChecks' => json_encode($details['permissionsAndChecks'] ?? null),
      'locationDetails' => json_encode($details['locationDetails'] ?? null),
      'networkDetails' => json_encode($details['networkDetails'] ?? null),
      'restrictions' => json_encode($details['restrictions'] ?? null),
      'accountDetails' => json_encode($details['accountDetails'] ?? null),
      'osDetails' => json_encode($details['osDetails'] ?? null),
      'securityDetails' => json_encode($details['securityDetails'] ?? null),
      'androidConfigs' => json_encode($details['androidConfigs'] ?? null),
      'appleConfigs' => json_encode($details['appleConfigs'] ?? null),
      'installedApps' => json_encode($details['installedApps'] ?? null),
      'miscellaneous' => json_encode($details['miscellaneous'] ?? null),
    ]);

    return ['success' => true, "payload" => json_encode(["device" => $deviceCreated, "deviceDetails" => $deviceDetailsCreated])];
  } catch (\Exception $e) {
    \App\Exceptions\CatchedExceptionHandler::handle($e);
    return ['success' => false, 'message' => 'Error creating device'];
  }
}*/
