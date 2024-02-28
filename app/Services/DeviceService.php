<?php

namespace App\Services;

use App\Entities\UemDeviceEntity;
use App\Repositories\UemDeviceRepository;
use App\Http\Controllers\UemDeviceController;
use Carbon\Carbon;
use Exception;

class DeviceService
{
  protected $googleService;

  public function __construct(AndroidEnterpriseService $googleService = null)
  {
    $this->googleService = $googleService;
  }

  public function updateAgentMdmInfo($deviceId, $mdmToken, $agentVersion): void
  {
    // Assuming you have a Device model and corresponding methods to update it
//    $device = UemDeviceEntity::find($deviceId);
    $repository = app(UemDeviceRepository::class);
    $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
    $device = $repository->getDeviceById($deviceId);
    if (!$device) {
      throw new Exception("Device not found", 404);
    }

    // Update device details here
    $device->update([
      'mdm_token' => $mdmToken,
      'agent_version' => $agentVersion,
      'updated_at' => Carbon::now(),
    ]);
  }

  public function hasGooglePlayServices($deviceId)
  {
    $hasAndroidPlayServices = false;
//    $deviceDetails =  UemDeviceEntity::find('deviceId', $deviceId);
    $repository = app(UemDeviceRepository::class);
    $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
    $deviceDetails = $repository->getDeviceDetailsById(id: $deviceId);
    dd($deviceDetails);

    if ($deviceDetails && $deviceDetails->androidConfigs) {
      $androidConfigs = json_decode($deviceDetails->androidConfigs);
      if ($androidConfigs && isset($androidConfigs->hasAndroidPlayServices)) {
        $hasAndroidPlayServices = $androidConfigs->hasAndroidPlayServices;
      }
    }
    return $hasAndroidPlayServices;
  }

  public function installDefaultApps($deviceId)
  {
    // Interact with Google's API or other services to install default apps

    return true;
  }

  public function installDefaultApks($deviceId)
  {
    // Based on the device brand and Play Services status, handle APK installation

    return true;
  }
}
