<?php

namespace App\Http\Controllers;

use App\Dtos\Controller\ControllerResponse;
use App\Entities\ProfileEntity;
use App\Models\Profile;
use App\Models\Payload;
use App\Models\OldPayload;
use App\Models\OldProfile;
use App\Repositories\UemPayloadRepository;
use App\Repositories\UemProfileRepository;
use App\Services\PayloadDataRetriver;
use DateTime;
use Illuminate\Support\Facades\Log;

class UemProfileController extends Controller
{
  //GET ON ALL PROFILES (PAGE FILTERS INCLUDED)
  public function list($filters = [])
  {
    try {
      $paginationOptions = [];
      $orderByFilter = [];
      if (isset($filters["paginate"])) {
        $paginationOptions["rowsPerPage"] = (int)$filters["rowsPerPage"] ?? 15;
        $paginationOptions["page"] = (int)$filters["page"] ?? 1;
      }
      if (isset($filters["orderBy"])) {
        foreach ($filters["orderBy"] as $orderByFilter) {
          $orderByFilter[$orderByFilter['attribute']] = $orderByFilter['order'];
        }
      }
      $whereConditions = [];
      // FILTERS _______________ OPERATING SYSTEM
      if (isset($filters["operatingSystem"]) && $filters["operatingSystem"] != '') {
        $whereConditions[] = [
          'attribute' => 'operatingSystem',
          'operator' => '=',
          'value' => $filters["operatingSystem"],
        ];
      }
      // FILTERS _______________ DATE ____ BETWEEN START AND END
      if ((isset($filters["startDate"]) && $filters["startDate"] != '') && (isset($filters["endDate"]) && $filters["endDate"] != '')) {
        $whereConditions[] = [
          'attribute' => 'createdAt',
          'operator' => 'between',
          'value' => [$filters["startDate"], $filters["endDate"]],
        ];
      }

      /**
       * @var UemProfileRepository $repository
       */
      $repository = app(UemProfileRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listPaginated = $repository->listPaginatedWithPayloads(paginationOptions: $paginationOptions, whereConditions: $whereConditions, orderByConditions: $orderByFilter);
      return $listPaginated->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  public function getProfileById($id)
  {
    try {
      /**
       * @var UemProfileRepository $repository
       */
      $repository = app(UemProfileRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $profile = $repository->get($id);
      return $profile->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  //GET SINGLE PAYLOAD BY PAYLOADID
  public function getPayloadById($id)
  {
    try {
      /**
       * @var UemPayloadRepository $repository
       */
      $repository = app(UemPayloadRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $profile = $repository->get($id);
      return $profile->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  //DELETE PROFILE BY PROFILEID
  public function delete($id): ControllerResponse
  {
    try {
      /**
       * @var UemProfileRepository $repository
       */
      $repository = app(UemProfileRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $listPaginated = $repository->delete($id);
      return $listPaginated->formatControllerResponse();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  //POST CREATE PROFILE
  public function create($request = [])
  {
    try {
      $profileToCreate = new ProfileEntity([
        'profileDisplayName' => $request['name'],
        'profileDescription' => $request['description'],
        'profileType' => $request['profileType'],
        'profileUUID' => $request['profileUUID'],
        'operatingSystem' => $request['operatingSystem'],
        'profileExpirationDate' => $request['profileExpirationDate'] ?? null,
        'removalDate' => $request['removalDate'] ?? null,
        'durationUntilRemoval' => $request['durationUntilRemoval'] ?? null,
        'durationUntilRemovalDate' => $request['durationUntilRemovalDate'] ?? null,
        'consentText' => $request['consentText'] ?? null,
        'profileRemovalDisallowed' => $request['profileRemovalDisallowed'],
        'profileScope' => $request['profileScope'] ?? null,
        'profileOrganization' => $request['profileOrganization'] ?? null,
        'isEncrypted' => $request['isEncrypted'],
        'profileVersion' => $request['profileVersion'],
        'onSingleDevice' => $request['onSingleDevice'],
        'limitOnDates' => $request['limitOnDates'] ?? null,
        'limitOnWifiRange' => $request['limitOnWifiRange'] ?? null,
        'limitOnPublicIps' => $request['limitOnPublicIps'] ?? null,
        'home' => $request['home'],
        'copeMaster' => $request['copeMaster'],
        'enabled' => $request['enabled'],
        'createdAt' => DateTime::createFromFormat('H:i:s d-m-Y', $request['datetime'])->format('Y-m-d H:i:s'),
      ]);
      /**
       * @var UemProfileRepository $profileRepo
       */
      $profileRepo = app(UemProfileRepository::class);
      $profileRepo->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);

      if (isset($request['payloadList'])) {
        $payloads = [];
        foreach ($request['payloadList'] as $payloadItem) {
          $payloadItem['payloadUUID'] = $payloadItem['payloadUUID'] ?? 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC6';
          $payloadItem['applePayloadType'] = $payloadItem['applePayloadType'] ?? 'com.apple.payloads';
          $payloadItem['payloadVersion'] = $payloadItem['payloadVersion'] ?? 1;
          $payloadItem['createdAt'] = DateTime::createFromFormat('H:i:s d-m-Y', $request['datetime'])->format('Y-m-d H:i:s');

          $params = [];
          foreach ($payloadItem['Fields'] as $field) {
            $params[] = [
              'id' => $field['id'],
              'value' => $field['value'],
            ];
          }
          $payloadItem['params'] = $params;
          $payloads[] = $payloadItem;
        }
        $repoResponse = $profileRepo->storeWithPayloads($profileToCreate, $payloads);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE_WITH_PAYLOADS');
      } else {
        $repoResponse = $profileRepo->store($profileToCreate);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE');
      }
      return new ControllerResponse(true, null, 200);
    } catch (\Exception|\Throwable $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  // POST LOAD PROFILE FROM FILE
  public function loadfromfile($request = [])
  {
    try {
      // TODO: all
      // create a new profile and create new payloads from the payloadList

      $profileToCreate = new ProfileEntity([
        'profileDisplayName' => $request['profileDisplayName'],
        'profileDescription' => $request['profileDescription'],
        'profileType' => $request['profileType'],
        'profileUUID' => $request['profileUUID'],
        'operatingSystem' => $request['operatingSystem'],
        'profileExpirationDate' => $request['profileExpirationDate'] ?? null,
        'removalDate' => $request['removalDate'] ?? null,
        'durationUntilRemoval' => $request['durationUntilRemoval'] ?? null,
        'durationUntilRemovalDate' => $request['durationUntilRemovalDate'] ?? null,
        'consentText' => $request['consentText'] ?? null,
        'profileRemovalDisallowed' => $request['profileRemovalDisallowed'],
        'profileScope' => $request['profileScope'] ?? null,
        'profileOrganization' => $request['profileOrganization'] ?? null,
        'isEncrypted' => $request['isEncrypted'],
        'profileVersion' => $request['profileVersion'],
        'onSingleDevice' => $request['onSingleDevice'],
        'limitOnDates' => $request['limitOnDates'] ?? null,
        'limitOnWifiRange' => $request['limitOnWifiRange'] ?? null,
        'limitOnPublicIps' => $request['limitOnPublicIps'] ?? null,
        'home' => $request['home'],
        'copeMaster' => $request['copeMaster'],
        'enabled' => $request['enabled'],
        'createdAt' => now()->format('Y-m-d H:i:s')
      ]);
      /**
       * @var UemProfileRepository $profileRepo
       */
      $profileRepo = app(UemProfileRepository::class);
      $profileRepo->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);


      if (isset($request['payloadList'])) {
        $payloads = [];
        foreach ($request['payloadList'] as $payloadItem) {
          $payloadItem['payloadUUID'] = $payloadItem['payloadUUID'] ?? 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC6';
          $payloadItem['PayloadName'] = $payloadItem['payloadDisplayName'];
          $payloadItem['applePayloadType'] = $payloadItem['applePayloadType'] ?? 'com.apple.payloads';
          $payloadItem['payloadVersion'] = $payloadItem['payloadVersion'] ?? 1;
          $payloadItem['createdAt'] = now()->format('Y-m-d H:i:s');

          $payloadItem['params'] = json_decode($payloadItem['params']);
          $payloadItem['config'] = (array) json_decode($payloadItem['config']);
          $payloads[] = $payloadItem;
        }
        $repoResponse = $profileRepo->storeWithPayloads($profileToCreate, $payloads);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE_WITH_PAYLOADS');
      } else {
        $repoResponse = $profileRepo->store($profileToCreate);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE');
      }

      return new ControllerResponse(true, null, 200);
    } catch (\Exception|\Throwable $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  //POST UPDATE EXISTING PROFILE
  public function update($id, $request = []): ControllerResponse
  {
    try {
      $profileToCreate = new ProfileEntity([
        'profileDisplayName' => $request['profileDisplayName'],
        'profileDescription' => $request['profileDescription'],
        'profileType' => $request['profileType'],
        'profileUUID' => $request['profileUUID'],
        'operatingSystem' => $request['operatingSystem'],
        'profileExpirationDate' => $request['profileExpirationDate'] ?? null,
        'removalDate' => $request['removalDate'] ?? null,
        'durationUntilRemoval' => $request['durationUntilRemoval'] ?? null,
        'durationUntilRemovalDate' => $request['durationUntilRemovalDate'] ?? null,
        'consentText' => $request['consentText'] ?? null,
        'profileRemovalDisallowed' => $request['profileRemovalDisallowed'],
        'profileScope' => $request['profileScope'] ?? null,
        'profileOrganization' => $request['profileOrganization'] ?? null,
        'isEncrypted' => $request['isEncrypted'],
        'profileVersion' => $request['profileVersion'],
        'onSingleDevice' => $request['onSingleDevice'],
        'limitOnDates' => $request['limitOnDates'] ?? null,
        'limitOnWifiRange' => $request['limitOnWifiRange'] ?? null,
        'limitOnPublicIps' => $request['limitOnPublicIps'] ?? null,
        'home' => $request['home'],
        'copeMaster' => $request['copeMaster'],
        'enabled' => $request['enabled'],
        'createdAt' => DateTime::createFromFormat('H:i:s d-m-Y', $request['datetime'])->format('Y-m-d H:i:s'),

      ]);
      /**
       * @var UemProfileRepository $profileRepo
       */
      $profileRepo = app(UemProfileRepository::class);
      $profileRepo->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);

      if (isset($request['payloadList'])) {
        $payloads = [];
        foreach ($request['payloadList'] as $payloadItem) {
          $payloadItem['payloadUUID'] = $payloadItem['payloadUUID'] ?? 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC6';
          $payloadItem['applePayloadType'] = $payloadItem['applePayloadType'] ?? 'com.apple.payloads';
          $payloadItem['payloadVersion'] = $payloadItem['payloadVersion'] ?? 1;
          $payloadItem['createdAt'] = DateTime::createFromFormat('H:i:s d-m-Y', $request['datetime'])->format('Y-m-d H:i:s');

          $params = [];
          foreach ($payloadItem['Fields'] as $field) {
            $params[] = [
              'id' => $field['id'],
              'value' => $field['value'],
            ];
          }
          $payloadItem['params'] = json_encode($params);
          $payloads[] = $payloadItem;
        }
        $repoResponse = $profileRepo->update($id, $profileToCreate, $payloads);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE_WITH_PAYLOADS');
      } else {
        $repoResponse = $profileRepo->update($id, $profileToCreate);
        if (!$repoResponse->isSuccess())
          throw new \Exception('CANT_CREATE_PROFILE');
      }
      return new ControllerResponse(true, null, 200);
    } catch (\Exception|\Throwable $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return new ControllerResponse(false, null, 500);
    }
  }

  //GET PAYLOADLIST BY OSTYPE
  /*public function getPayloadList($osType = null, $payloadInfo = [])
  {
    //$authenticatedUser = auth()->user();
    //$query = $this->_getModel()::on($this->_getDbConnection());
    if (isset($payloadInfo)) {
      // Use $payloadInfo to filter payloads
      $payloadDisplayNames = collect($payloadInfo)->pluck('payloadDisplayName')->toArray();
    }

    if ($osType == null || ($osType != "android" && $osType != "windows" && $osType != "ios" && $osType != "mixed")) {
      return "Unsupported OS type";
    } else {

      $payloads = [];

      $payloadDirectory = "../app/Payloads/" . ucfirst($osType) . "/";
      $baseNamespace = "App\\Payloads\\" . ucfirst($osType) . "\\";

      foreach (glob($payloadDirectory . "*.php") as $payloadFilename) {
        require_once $payloadFilename;
        $className = pathinfo($payloadFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        $compatibility = true;
        //$compatibility = $class->checkCompatibility(osType: $osType);

        if ($compatibility) {
          $payload = [
            "PayloadName" => $className,
            "icon" => $class->getIcon(),
            "config" => $class->getConfig(),
            "Fields" => $this->isPayloadFilenameDifferent($className, $payloadDisplayNames)
              ? $class->getSchema()
              : $this->updateFieldValues($className, $payloadInfo, $class->getSchema()),
          ];

          $payloads[] = $payload;
        }
      }

      $commonDirectory = "../app/Payloads/Common/";
      $baseNamespace = "App\\Payloads\\Common\\";
      foreach (glob($commonDirectory . "*.php") as $commonFilename) {
        $className = pathinfo($commonFilename, PATHINFO_FILENAME);
        $fullClassName = $baseNamespace . $className;
        $class = new $fullClassName();
        $compatibility = ($osType != "mixed") ? $class->checkCompatibility($osType) : true;

        if ($compatibility) {
          $payload = [
            "PayloadName" => $className,
            "icon" => $class->getIcon(),
            "config" => $class->getConfig(),
            "osCategorized" => true,
            "Fields" => $this->isPayloadFilenameDifferent($className, $payloadDisplayNames)
              ? $class->getSchema()
              : $this->updateFieldValues($className, $payloadInfo, $class->getSchema()),
          ];
          $payloads[] = $payload;
        }
      }

      return $payloads;

    }

  }*/
  public function getPayloadList(string $osType): ControllerResponse
  {
    /**
     * @var PayloadDataRetriver $payloadsRetriever
     */
    $payloadsRetriever = app(PayloadDataRetriver::class);
    return $payloadsRetriever->getData($osType);

  }
}
