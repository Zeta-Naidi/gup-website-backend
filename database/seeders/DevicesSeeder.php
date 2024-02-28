<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UemDevice;
use App\Models\UemDeviceDetails;
use Illuminate\Support\Facades\Log;

class DevicesSeeder extends Seeder
{
  /**
   * Run the devices seeds.
   */
  public function run()
  {
    $numberOfDevices = 25;

    for ($i = 1; $i <= $numberOfDevices; $i++) {
      $device = UemDevice::factory()->connection("testing_mdm_prova_d3tGk")->create(['id' => $i]);
      $deviceDetails = UemDeviceDetails::factory()->connection("testing_mdm_prova_d3tGk")->create(['deviceId' => $device->id]);
    }
  }
}
