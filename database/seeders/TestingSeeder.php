<?php

namespace Database\Seeders;

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Entities\TagEntity;
use App\Repositories\TagRepository;
use App\Services\DatabaseDataRetriever;
use App\Traits\RefreshTestingDatabase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestingSeeder extends Seeder
{
    use RefreshTestingDatabase;

    /**
     * Run the database seeds.
     */
    public function run($database): void
    {
      if($database === "db_users_mssp_d3tGkTEST"){
        $this->SeedDatabaseUsers($database);
      }else if($database === "testing_mdm_prova1_d3tGkTEST"){
        $this->SeedDatabaseMdm($database);
      }else if($database === "testing_distributor_1_d3tGkTEST"){
        $this->SeedDatabaseDistributors($database);
      }else{
        // nothing
      }
    }

    private function SeedDatabaseUsers(string $database): void
    {

      $faker = $this->getFaker();

      $createdDTO = new DTODatabaseCreateInput(
        $database,
        'users',
        [
          'nameDatabaseConnection' => "db_users_mssp_d3tGkTEST",
          'password' => $faker->password,
          'email' => $faker->email,
          'username' => $faker->userName,
          'companyName' => $faker->name,
          'levelAdmin' => $faker->numberBetween(0, 2),
        ]
      );
      $retriever = app(DatabaseDataRetriever::class, ['parameters' => $createdDTO]);
      /*        $rolesUserDTO = new DTODatabaseCreateInput(
                $database,
                'roles_users',
                [
                  'userId' => $i,
                ]
              );*/
      $retriever->execute();
    }

  private function SeedDatabaseMdm(string $database): void
  {
    $faker = $this->getFaker();

    // user without MDM db access :
   /* DB::connection("db_users_mssp_d3tGkTEST")->table("users")->insert([
      'nameDatabaseConnection' => "testing_mdm_prova1_d3tGkTEST",
      'password' => Hash::make("password", ["rounds" => 14]),
      'email' => $faker->email,
      'username' => $faker->userName,
      'companyName' => $faker->name,
      'levelAdmin' => $faker->numberBetween(0, 2),
    ]);*/

    $usersCount = 2;
    for ($i = 0; $i < $usersCount; $i++) {
      DB::connection("db_users_mssp_d3tGkTEST")->table("users")->insert([
        'nameDatabaseConnection' => "testing_mdm_prova1_d3tGkTEST",
        'password' => Hash::make("password", ["rounds" => 14]),
        'email' => $faker->email,
        'username' => $faker->userName,
        'companyName' => $faker->name,
        'levelAdmin' => $faker->numberBetween(0, 2),
      ]);
    }


    // TAGS
    DB::connection($database)->table("tags")->insert(['tagName' => $faker->name]);

    //DEVICES
   $deviceCount = 20;
    for ($i = 0; $i < $deviceCount; $i++) {
      $jsonFaker = json_encode([
        "property1" => $faker->word,
        "property2" => $faker->sentence,
      ]);
      DB::connection($database)->table("devices")->insert(
        [
          "parentDeviceId" => $faker->numerify,
          "deviceName" => $i === 1 ? "TestDeviceNameFilterList" : $faker->word,
          "modelName" => $faker->word,
          "macAddress" => $faker->word,
          "meid" => substr($faker->word, 0, 10),
          "udid" => $faker->word,
          "vendorId" => $faker->word,
          "osArchitecture" => $faker->word,
          "osType" => substr($faker->word, 0, 10),
          "osEdition" => substr($faker->word, 0, 10),
          "osVersion" => substr($faker->word, 0, 10),
          "manufacturer" => $faker->word,
          "serialNumber" => $i === 1 ? "TestDeviceSerialFilterList" : $faker->word,
          "imei" => $faker->word,
          "isDeleted" => $faker->boolean,
          "phoneNumber" => $faker->phoneNumber,
          "isOnline" => $faker->boolean,
          "brand" => $faker->word,
          "networkIdentity" => $jsonFaker,
          "configuration" => $jsonFaker,
          "deviceIdentity" => $jsonFaker,
          "createdAt" => date(now()),
        ]
      );
    }

    //TAGS DEVICES
    DB::connection($database)->table("tags_devices")->insert(['tag_id' => 1, 'device_id' => 1]);

    //TAGS USERS
    DB::connection($database)->table("tags_users")->insert(['tag_id' => 1, 'user_id' => 1]);

    $profiles = [
      [
        'id' => 1,
        'profileDisplayName' => 'ORGANIZATION_PROFILE',
        'profileDescription' => NULL,
        'profileType' => 'Configuration',
        'profileUUID' => 'FA00EBA4-FFC6-4BB6-9F81-F101D0A3336F',
        'operatingSystem' => 'Apple',
        'profileExpirationDate' => NULL,
        'removalDate' => NULL,
        'durationUntilRemoval' => NULL,
        'durationUntilRemovalDate' => NULL,
        'consentText' => NULL,
        'profileRemovalDisallowed' => 0,
        'profileScope' => NULL,
        'profileOrganization' => NULL,
        'isEncrypted' => 0,
        'profileVersion' => 1,
        'onSingleDevice' => 0,
        'limitOnDates' => NULL,
        'limitOnWifiRange' => NULL,
        'limitOnPublicIps' => NULL,
        'home' => 0,
        'copeMaster' => 0,
        'enabled' => 1,
        'createdAt' => now(),
      ],
      [
        'id' => 2,
        'profileDisplayName' => 'TEACHERS_PROFILE',
        'profileDescription' => NULL,
        'profileType' => 'Configuration',
        'profileUUID' => 'A6CAAAC9-8C95-4883-8A9B-38F2333FA28D',
        'operatingSystem' => 'Android',
        'profileExpirationDate' => NULL,
        'removalDate' => NULL,
        'durationUntilRemoval' => NULL,
        'durationUntilRemovalDate' => NULL,
        'consentText' => NULL,
        'profileRemovalDisallowed' => 0,
        'profileScope' => NULL,
        'profileOrganization' => NULL,
        'isEncrypted' => 0,
        'profileVersion' => 1,
        'onSingleDevice' => 0,
        'limitOnDates' => NULL,
        'limitOnWifiRange' => NULL,
        'limitOnPublicIps' => NULL,
        'home' => 0,
        'copeMaster' => 0,
        'enabled' => 1,
        'createdAt' => now(),
      ],
      [
        'id' => 3,
        'profileDisplayName' => 'STUDENTS_PROFILE',
        'profileDescription' => NULL,
        'profileType' => 'Configuration',
        'profileUUID' => '22821B70-E240-40C5-8314-03936C945825',
        'operatingSystem' => 'Windows',
        'profileExpirationDate' => NULL,
        'removalDate' => NULL,
        'durationUntilRemoval' => NULL,
        'durationUntilRemovalDate' => NULL,
        'consentText' => NULL,
        'profileRemovalDisallowed' => 0,
        'profileScope' => NULL,
        'profileOrganization' => NULL,
        'isEncrypted' => 0,
        'profileVersion' => 1,
        'onSingleDevice' => 0,
        'limitOnDates' => NULL,
        'limitOnWifiRange' => NULL,
        'limitOnPublicIps' => NULL,
        'home' => 0,
        'copeMaster' => 0,
        'enabled' => 1,
        'createdAt' => now(),
      ],
      [
        'id' => 4,
        'profileDisplayName' => 'CLASSROOM_PROFILE',
        'profileDescription' => NULL,
        'profileType' => 'Configuration',
        'profileUUID' => 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC5',
        'operatingSystem' => 'Mixed',
        'profileExpirationDate' => NULL,
        'removalDate' => NULL,
        'durationUntilRemoval' => NULL,
        'durationUntilRemovalDate' => NULL,
        'consentText' => NULL,
        'profileRemovalDisallowed' => 0,
        'profileScope' => NULL,
        'profileOrganization' => NULL,
        'isEncrypted' => 0,
        'profileVersion' => 1,
        'onSingleDevice' => 0,
        'limitOnDates' => NULL,
        'limitOnWifiRange' => NULL,
        'limitOnPublicIps' => NULL,
        'home' => 0,
        'copeMaster' => 0,
        'enabled' => 1,
        'createdAt' => now(),
      ],
    ];

    foreach ($profiles as $profile) {
      DB::connection($database)
        ->table("profiles")
        ->insert($profile);
    }

    for ($i = 0; $i < count($profiles); $i++) {
      DB::connection($database)
        ->table("payloads")
        ->insert(
          [
            "payloadUUID" => "F46988E4-087E-4D4C-B7A3-71B51F8FCEC6",
            "profileId" => $i+1,
            "payloadDisplayName" => "General",
            "payloadDescription" => "Descrizione del profilo",
            "payloadOrganization" => $faker->word,
            "applePayloadType" => $faker->word,
            "params" => json_encode([['key' => 1, 'value' => "General"],['key' => 2, 'value' => true], ['key' => 3, 'value' => "Descrizione del profilo"], ['key' => 4, 'value' => "Main"]]),
            "payloadVersion" => $faker->numerify,
            "createdAt" => date(now()),
          ]
        );
    }

    // PAYLOADS
    $payloadsCount = 20;
    for ($i = 0; $i < $payloadsCount; $i++) {
      DB::connection($database)
        ->table("payloads")
        ->insert(
          [
            "payloadUUID" => substr($faker->word, 0, 128),
            "profileId" => $faker->numberBetween(1, 4),
            "payloadDisplayName" => $faker->word,
            "payloadDescription" => $faker->word,
            "payloadOrganization" => $faker->word,
            "applePayloadType" => $faker->word,
            "params" => json_encode(['key' => $faker->word, 'value' => $faker->word]),
            "payloadVersion" => $faker->numerify,
            "createdAt" => date(now()),
          ]
        );
    }

  }

  private function SeedDatabaseDistributors(string $database): void
  {
    $faker = $this->getFaker();

    $createdUsersDTO = new DTODatabaseSelectInput(
      $database,
      'resellers',
      ['id']
    );
    $retriever = app(DatabaseDataRetriever::class, ['parameters' => $createdUsersDTO]);

    // RESELLERS
    $resellersCount = 5;
    for ($i = 0; $i < $resellersCount; $i++) {
      $CreatedResellersDTO = new DTODatabaseCreateInput(
        $database,
        'resellers',
        [
          "chimpaResellerId"=> substr($faker->numerify, 0, 3),
          "name"=> $faker->name,
          "email"=> $faker->email,
        ]
      );
      $retriever->updateDTO($CreatedResellersDTO);
      $retrieverCreatedResellersDTO = $retriever->execute();
    }

    $numberOfresellers = DB::connection($database)->table('resellers')->count();

    // CLIENTS
    $clientsCount = 5;
    for ($i = 0; $i < $clientsCount; $i++) {
      DB::connection($database)->table("clients")->insert([
        "chimpaClientId"=>substr($faker->numerify, 0, 3),
        "resellerId"=> $faker->numberBetween(1, $numberOfresellers),
        "baseUrl"=> $faker->url,
        "host"=> $faker->word,
        "companyName"=> $faker->name,
        "devicesLastUpdate"=> $faker->date,
        "eventsLastUpdate"=> $faker->date,
        "appUsagesLastUpdate"=> $faker->date,
        "networkActivitiesLastUpdate"=> $faker->date,
        "countryCode"=> $faker->countryCode,
        "phone"=> $faker->phoneNumber,
        "email"=> $faker->email,
      ]);
    }


    $numberOfClients = DB::connection($database)->table('clients')->count();

    // DEVICES
    $deviceCount = 20;
    for ($i = 0; $i < $deviceCount; $i++) {
      DB::connection($database)->table("devices")->insert([
        "clientId" => $faker->numberBetween(1, $numberOfClients),
        "serialNumber" => $faker->uuid,
        "name" => $faker->word,
        "osType" => $faker->word,
        "osVersion" => $faker->numerify,
        "isEnrolled" => $faker->boolean,
        "isSupervised" => $faker->boolean,
        "isAgentOn" => $faker->boolean,

      ]);
    }
  }
}
