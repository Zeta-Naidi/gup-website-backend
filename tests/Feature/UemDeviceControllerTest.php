<?php

namespace Tests\Feature;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Services\DatabaseDataRetriever;
use App\Traits\RefreshTestingDatabase;
use Database\Seeders\TestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UemDeviceControllerTest extends TestCase
{
  use RefreshTestingDatabase;

  private string $databaseMDM = "testing_mdm_prova1_d3tGkTEST";
  private string $databaseMdmMigrationPath = "database/migrations/mdmDB";
  private string $databaseMssp = "db_users_mssp_d3tGkTEST";
  private string $databaseMsspMigrationPath = "database/migrations/usersDB";

  public function setUp(): void
  {
    parent::setUp();
    // Refresh the $databaseMDM and seed it with fake data
    $this->refreshAndSeedDatabase($this->databaseMDM, TestingSeeder::class);
  }

  /** @test */
  public function it_can_get_devices()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/uem/device');
    $response->assertStatus(200);

    $devices = $response->json()['payload'];
    $this->assertNotEmpty($devices);

    // aggingere altri campi
    foreach ($devices as $device) {
      $this->assertArrayHasKey('parentDeviceId', $device);
      $this->assertArrayHasKey('deviceName', $device);
      $this->assertArrayHasKey('windowsAgentVersion', $device);
      $this->assertArrayHasKey('isOnline', $device);
      $this->assertNotEmpty($device['parentDeviceId']);
      $this->assertNotEmpty($device['deviceName']);
      $this->assertNotEmpty($device['windowsAgentVersion']);
      $this->assertIsNumeric($device['isOnline']);
    }
  }

  /** @test */
  public function it_can_get_devices_with_filter_serialOrName()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/uem/device',
      ['serialOrName' => "TestDeviceNameFilterList"]
    );
    $response->assertStatus(200);

    $device = $response->json()['payload'][0];
    $this->assertNotEmpty($device);

    // aggingere altri campi
    $this->assertArrayHasKey('parentDeviceId', $device);
    $this->assertArrayHasKey('deviceName', $device);
    $this->assertArrayHasKey('windowsAgentVersion', $device);
    $this->assertArrayHasKey('isOnline', $device);
    $this->assertNotEmpty($device['parentDeviceId']);
    $this->assertNotEmpty($device['deviceName']);
    $this->assertNotEmpty($device['windowsAgentVersion']);
    $this->assertIsNumeric($device['isOnline']);
    $this->assertEquals('TestDeviceNameFilterList', $device['deviceName']);
  }

  /** @test */
  public function it_can_get_devices_with_filter_status()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/uem/device',
      ['serialOrName' => "TestDeviceNameFilterList"] //TODO: change to status
    );
    $response->assertStatus(200);

    $device = $response->json()['payload'][0];
    $this->assertNotEmpty($device);

    // aggingere altri campi
    $this->assertArrayHasKey('parentDeviceId', $device);
    $this->assertArrayHasKey('deviceName', $device);
    $this->assertArrayHasKey('windowsAgentVersion', $device);
    $this->assertArrayHasKey('isOnline', $device);
    $this->assertNotEmpty($device['parentDeviceId']);
    $this->assertNotEmpty($device['deviceName']);
    $this->assertNotEmpty($device['windowsAgentVersion']);
    $this->assertIsNumeric($device['isOnline']);
    $this->assertEquals('TestDeviceNameFilterList', $device['deviceName']);
  }

  /** @test */
  public function it_can_get_devices_with_pagination()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', '/api/uem/device/',
      [
        'rowsPerPage' => 10,
        'page' => 0
      ]
    );
    $response->assertStatus(200);

    $devices = $response->json()['payload']['data'];
    $this->assertNotEmpty($devices);

    // Check pagination keys
    $payload = $response->json()['payload'];
    $paginationKeys = ['current_page', 'data', 'first_page_url', 'from', 'last_page', 'last_page_url', 'links', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'total'];
    foreach ($paginationKeys as $key) {
      $this->assertArrayHasKey($key, $payload);
    }
    $this->assertCount(10, $devices);

    // aggingere altri campi
    foreach ($devices as $device) {
      $this->assertArrayHasKey('parentDeviceId', $device);
      $this->assertArrayHasKey('deviceName', $device);
      $this->assertArrayHasKey('windowsAgentVersion', $device);
      $this->assertArrayHasKey('isOnline', $device);
      $this->assertNotEmpty($device['parentDeviceId']);
      $this->assertNotEmpty($device['deviceName']);
      $this->assertNotEmpty($device['windowsAgentVersion']);
      $this->assertIsNumeric($device['isOnline']);
    }
  }

  /** @test */
  public function it_can_get_devices_with_id()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/uem/device/2');
    $response->assertStatus(200);

    $device = $response->json()['payload'][0];
    $this->assertNotEmpty($device);

    $this->assertArrayHasKey('deviceName', $device);
    $this->assertEquals(2, $device['id']);
    $this->assertEquals('TestDeviceNameFilterList', $device['deviceName']);
  }

  /** @test */
  public function it_can_create_devices()
  {
    Auth::loginUsingId(1);

    $faker = $this->getFaker();
    $jsonFaker = json_encode([
      "property1" => $faker->word,
      "property2" => $faker->sentence,
    ]);

    $this->json('post', 'api/uem/device/create',
    [
      "deviceDetails" => $jsonFaker,
      "parentDeviceId" => $faker->numerify,
      "deviceName" => "IASCGUH67r54VYIOASIOHVU",
      "modelName" => $faker->word,
      "macAddress" => $faker->word,
      "meid" => substr($faker->word, 0, 10),
      "udid" => $faker->word,
      "vendorId" => $faker->word,
      "osArchitecture" => $faker->word,
      "osType" => substr($faker->word, 0, 10),
      "abbinationCode" => $faker->word,
      "serialNumber" => $faker->word,
      "imei" => $faker->word,
      "isDeleted" => $faker->boolean,
      "phoneNumber" => $faker->phoneNumber,
      "assignedLicense" => $faker->word,
      "isAndroidOem" => $faker->boolean,
      "isOnline" => $faker->boolean,
      "brand" => $faker->word,
      "hasAndroidPlayServices" => $faker->boolean,
      "agentFlavor" => $faker->word,
      "windowsAgentVersion" => substr($faker->word, 0, 10),
      "configuration" => $jsonFaker,
      "deviceEntity" => $jsonFaker
    ]
    )->assertStatus(200);


    $response = $this->json('get', 'api/uem/device',
      ['serialOrName' => "IASCGUH67r54VYIOASIOHVU"]
    );
    $response->assertStatus(200);

    $device = $response->json()['payload'];
    $this->assertNotEmpty($device);

    $this->assertEquals("IASCGUH67r54VYIOASIOHVU", $device[0]['deviceName']);
  }
}
