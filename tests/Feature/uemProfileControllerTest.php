<?php

namespace Tests\Feature;

use App\Traits\RefreshTestingDatabase;
use Database\Seeders\TestingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class uemProfileControllerTest extends TestCase
{
  use RefreshTestingDatabase;

  private string $databaseMDM = "testing_mdm_prova1_d3tGkTEST";
  private string $databaseMdmMigrationPath = "database/migrations/mdmDB";

  public function setUp(): void
  {
    parent::setUp();
    $this->refreshAndSeedDatabase($this->databaseMDM, TestingSeeder::class);
  }

  /** @test */
  public function it_can_get_profiles()
  {
    Auth::loginUsingId(3);

    $response = $this->json('get', '/api/uem/profile/');
    $response->assertStatus(200);

    $profiles = $response->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    // aggingere altri campi
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertArrayHasKey('profileDescription', $profile);
      $this->assertArrayHasKey('profileType', $profile);
      $this->assertArrayHasKey('profileUUID', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);
      $this->assertNotEmpty($profile['profileType']);
      $this->assertNotEmpty($profile['profileUUID']);
      // payloads
      $this->assertNotEmpty($profile['payloadList']);
      foreach ($profile['payloadList'] as $key => $payload) {
        if($key === 0){
          // check that every payload has: general payload
          $this->assertEquals('General', $payload['payloadDisplayName']);
          $this->assertEquals('Descrizione del profilo', $payload['payloadDescription']);
          $this->assertEquals('[{"key": 1, "value": "General"}, {"key": 2, "value": true}, {"key": 3, "value": "Descrizione del profilo"}, {"key": 4, "value": "Main"}]', $payload['params']);
        }
        $this->assertArrayHasKey('payloadDisplayName', $payload);
        $this->assertArrayHasKey('payloadDescription', $payload);
        $this->assertArrayHasKey('payloadOrganization', $payload);
        $this->assertArrayHasKey('payloadUUID', $payload);
        $this->assertArrayHasKey('params', $payload);
        $this->assertArrayHasKey('createdAt', $payload);
        $this->assertNotEmpty($payload['payloadDisplayName']);
        $this->assertNotEmpty($payload['payloadDescription']);
        $this->assertNotEmpty($payload['payloadOrganization']);
        $this->assertNotEmpty($payload['payloadUUID']);
        $this->assertNotEmpty($payload['params']);
        $this->assertNotEmpty($payload['createdAt']);
      }
    }
  }

  /** @test */
  public function it_can_get_profiles_with_pagination()
  {
    Auth::loginUsingId(3);

    $filters = [
      'paginate' => true,
      'page' => 1,
      'rowsPerPage' => 10,
      'operatingSystem' => '',
      'startDate' => '',
      'endDate' => '',
    ];

    $response = $this->call('get', '/api/uem/profile/', [
      'filters' => json_encode($filters)
    ]);

    $response->assertStatus(200);

    $profiles = $response->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    // Check pagination keys
    $payload = $response->json()['payload'];
    $paginationKeys = ['current_page', 'last_page', 'per_page', 'total'];
    foreach ($paginationKeys as $key) {
      $this->assertArrayHasKey($key, $payload);
    }
    $this->assertLessThanOrEqual(10, count($profiles));

    // aggingere altri campi
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertArrayHasKey('profileDescription', $profile);
      $this->assertArrayHasKey('profileType', $profile);
      $this->assertArrayHasKey('profileUUID', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);
      $this->assertNotEmpty($profile['profileType']);
      $this->assertNotEmpty($profile['profileUUID']);
      // payloads
      $this->assertNotEmpty($profile['payloadList']);
      foreach ($profile['payloadList'] as $key => $payload) {
        if($key === 0){
          // check that every payload has: general payload
          $this->assertEquals('General', $payload['payloadDisplayName']);
          // $this->assertEquals('Descrizione del profilo', $payload['payloadDescription']);
          $this->assertEquals('[{"key": 1, "value": "General"}, {"key": 2, "value": true}, {"key": 3, "value": "Descrizione del profilo"}, {"key": 4, "value": "Main"}]', $payload['params']);
        }
        $this->assertArrayHasKey('payloadDisplayName', $payload);
        $this->assertArrayHasKey('payloadDescription', $payload);
        $this->assertArrayHasKey('payloadOrganization', $payload);
        $this->assertArrayHasKey('payloadUUID', $payload);
        $this->assertArrayHasKey('params', $payload);
        $this->assertArrayHasKey('createdAt', $payload);
        $this->assertNotEmpty($payload['payloadDisplayName']);
        $this->assertNotEmpty($payload['payloadDescription']);
        $this->assertNotEmpty($payload['payloadOrganization']);
        $this->assertNotEmpty($payload['payloadUUID']);
        $this->assertNotEmpty($payload['params']);
        $this->assertNotEmpty($payload['createdAt']);
      }
    }
  }

  /** @test */
  public function it_can_get_profile_by_id()
  {
    Auth::loginUsingId(3);

    $response = $this->json('get', 'api/uem/profile/1');
    $response->assertStatus(200);

    $profile = $response->json()['payload'];
    $this->assertNotEmpty($profile);

    // aggingere altri campi
    $this->assertArrayHasKey('profileDisplayName', $profile);
    $this->assertArrayHasKey('profileDescription', $profile);
    $this->assertArrayHasKey('profileType', $profile);
    $this->assertArrayHasKey('profileUUID', $profile);
    $this->assertNotEmpty($profile['profileDisplayName']);
    $this->assertNotEmpty($profile['profileType']);
    $this->assertNotEmpty($profile['profileUUID']);
  }

  /** @test */
  public function it_can_create_profiles()
  {
    // todo: test ALL the possible creation payloads and profiles type ????
    Auth::loginUsingId(3);

    $profileToAdd = [
      'name' => 'TestAddProfile_e8uyrhdskm',
      'operatingSystem' => 'Windows',
      'description' => 'This is a test profile.',
      'devices' => [],
      'users' => [],
      'tags' => [],
      'datetime' => '12:00:00 07-02-2024',
      'payloadList' => [
        [
          'PayloadName' => 'General',
          'icon' => 'https://example.com/payload1-icon.png',
          'config' => [
            'show' => false,
          ],
          'Fields' => [
            [
              'id' => 1,
              'label' => 'Field 1',
              'field_id' => 'field1',
              'value' => 'TestAddProfile_e8uyrhdskm',
              'input_type' => 'text',
              'description' => 'Description for Field 1',
            ],
            [
              'id' => 2,
              'label' => 'Field 2',
              'field_id' => 'field2',
              'value' => true,
              'input_type' => 'checkbox',
              'description' => 'Description for Field 2',
            ],
            [
              'id' => 3,
              'label' => 'Field 3',
              'field_id' => 'field3',
              'value' => 'This is a test profile.',
              'input_type' => 'text',
              'description' => 'Description for Field 3',
            ],
            // Add more fields as needed
          ],
        ],
        // Add more payloads as needed
      ]
    ];

    $responseAdd = $this->json('post', 'api/uem/profile/create',
      $profileToAdd
    );
    $responseAdd->assertStatus(200);

    $responseGet = $this->json('get', '/api/uem/profile/');
    $responseGet->assertStatus(200);

    $profiles = $responseGet->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    $profileFound = false;
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);

      // Check if the added profile is found
      if ($profile['profileDisplayName'] === 'TestAddProfile_e8uyrhdskm') {
        $this->assertArrayHasKey('profileDisplayName', $profile);
        $this->assertArrayHasKey('profileDescription', $profile);
        $this->assertArrayHasKey('profileType', $profile);
        $this->assertArrayHasKey('profileUUID', $profile);
        $this->assertNotEmpty($profile['profileDisplayName']);
        $this->assertNotEmpty($profile['profileType']);
        $this->assertNotEmpty($profile['profileUUID']);
        // payloads
        $this->assertNotEmpty($profile['payloadList']);
        foreach ($profile['payloadList'] as $key => $payload) {
          if($key === 0){
            // check that every payload has: general payload
            $this->assertEquals('General', $payload['payloadDisplayName']);
          }
          $this->assertArrayHasKey('payloadDisplayName', $payload);
          $this->assertArrayHasKey('payloadDescription', $payload);
          $this->assertArrayHasKey('payloadOrganization', $payload);
          $this->assertArrayHasKey('payloadUUID', $payload);
          $this->assertArrayHasKey('params', $payload);
          $this->assertArrayHasKey('createdAt', $payload);
          $this->assertNotEmpty($payload['payloadDisplayName']);
          $this->assertNotEmpty($payload['payloadUUID']);
          $this->assertNotEmpty($payload['params']);
          $this->assertNotEmpty($payload['createdAt']);
        }
        $profileFound = true;
        break;
      }
    }

    $this->assertTrue($profileFound);
  }

  /** @test */
  public function it_can_update_profiles()
  {
    // gestione storico profile/payload
    Auth::loginUsingId(3);

    $profileToAdd = [
      'name' => 'TestAddProfile_65rdfcgvhu',
      'operatingSystem' => 'Windows',
      'description' => 'This is a test profile.',
      'devices' => [],
      'users' => [],
      'tags' => [],
      'datetime' => '12:00:00 07-02-2024',
      'payloadList' => [
        [
          'PayloadName' => 'General',
          'icon' => 'https://example.com/payload1-icon.png',
          'config' => [
            'show' => false,
          ],
          'Fields' => [
            [
              'id' => 1,
              'label' => 'Field 1',
              'field_id' => 'field1',
              'value' => 'TestAddProfile_65rdfcgvhu',
              'input_type' => 'text',
              'description' => 'Description for Field 1',
            ],
            [
              'id' => 2,
              'label' => 'Field 2',
              'field_id' => 'field2',
              'value' => true,
              'input_type' => 'checkbox',
              'description' => 'Description for Field 2',
            ],
            [
              'id' => 3,
              'label' => 'Field 3',
              'field_id' => 'field3',
              'value' => 'This is a test profile.',
              'input_type' => 'text',
              'description' => 'Description for Field 3',
            ],
            // Add more fields as needed
          ],
        ],
        // Add more payloads as needed
      ]
    ];

    $responseAdd = $this->json('post', 'api/uem/profile/create',
      $profileToAdd
    );
    $responseAdd->assertStatus(200);

    $responseGet = $this->json('get', '/api/uem/profile/');
    $responseGet->assertStatus(200);

    $profiles = $responseGet->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    $idProfileToUpdate = null;
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);

      if ($profile['profileDisplayName'] === 'TestAddProfile_65rdfcgvhu') {
        $idProfileToUpdate = $profile['id'];
        break;
      }
    }
    $this->assertIsNumeric($idProfileToUpdate);

    $updatedProfile = [
      'name' => 'TestAddProfile_65rdfcgvhu',
      'operatingSystem' => 'Windows',
      'description' => 'Profile updated',
      'devices' => [],
      'users' => [],
      'tags' => [],
      'datetime' => '12:00:00 07-02-2024',
      'payloadList' => [
        [
          'PayloadName' => 'General',
          'icon' => 'https://example.com/payload1-icon.png',
          'config' => [
            'show' => false,
          ],
          'Fields' => [
            [
              'id' => 1,
              'label' => 'Field 1',
              'field_id' => 'field1',
              'value' => 'TestAddProfile_65rdfcgvhu',
              'input_type' => 'text',
              'description' => 'Description for Field 1',
            ],
            [
              'id' => 2,
              'label' => 'Field 2',
              'field_id' => 'field2',
              'value' => true,
              'input_type' => 'checkbox',
              'description' => 'Description for Field 2',
            ],
            [
              'id' => 3,
              'label' => 'Field 3',
              'field_id' => 'field3',
              'value' => 'Changes done.',
              'input_type' => 'text',
              'description' => 'Description for Field 3',
            ],
            // Add more fields as needed
          ],
        ],
        [
          'PayloadName' => 'Sicurezza',
          'icon' => 'https://example.com/payload1-icon.png',
          'config' => [
            'show' => false,
            'title' => 'Payload 1',
            'description' => 'Configuration for Payload 1',
            'img' => 'https://example.com/payload1-img.png',
          ],
          'Fields' => [
            [
              'id' => 1,
              'label' => 'Field 1',
              'field_id' => 'field1',
              'value' => 'TestAddProfile_65rdfcgvhu',
              'input_type' => 'text',
              'description' => 'Description for Field 1',
            ],
            [
              'id' => 2,
              'label' => 'Field 2',
              'field_id' => 'field2',
              'value' => true,
              'input_type' => 'checkbox',
              'description' => 'Description for Field 2',
            ],
            [
              'id' => 3,
              'label' => 'Field 3',
              'field_id' => 'field3',
              'value' => 'Changes done.',
              'input_type' => 'text',
              'description' => 'Description for Field 3',
            ],
            // Add more fields as needed
          ],
        ],
        // Add more payloads as needed
      ]
    ];

    $deleteResponse = $this->json('put', '/api/uem/profile/update/'.$idProfileToUpdate,
      $updatedProfile
    );
    $deleteResponse->assertStatus(200);

    $getResponse = $this->json('get', '/api/uem/profile/');
    $getResponse->assertStatus(200);

    $profiles = $getResponse->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);

      if ($profile['profileDisplayName'] === 'TestAddUpdateProfile_u9br4jkfuhdf') {
        $this->assertEquals('Payload2', $profile['payloadList'][1]['payloadDisplayName']);
        $this->assertEquals('Profile updated', $profile['payloadList'][1]['profileDescription']);
        $this->assertEquals('Changes Done', json_decode($profile['payloadList'][1]['params'])[0]->value);

        break;
      }
    }

    // check profile changes (storico) in oldprofile and oldpayloads
    $oldprofile = DB::connection($this->databaseMDM)->table('oldprofiles')->where('profileId', '=', $idProfileToUpdate)->first();
    $oldpayloads = DB::connection($this->databaseMDM)->table('oldpayloads')->where('profileId', '=', $idProfileToUpdate)->get()->toArray();

    // aggiungere altri campi
    $this->assertNotEmpty($oldprofile->profileDisplayName);
    $this->assertNotEmpty($oldprofile->operatingSystem);
    $this->assertEquals("TestAddProfile_65rdfcgvhu", $oldprofile->profileDisplayName);
    $this->assertEquals("Windows", $oldprofile->operatingSystem);

    foreach ($oldpayloads as $oldpayload) {
      $this->assertNotEmpty($oldpayload->payloadDisplayName);
      $this->assertEquals('General', $oldpayload->payloadDisplayName);
      $this->assertEquals('This is a test profile.', json_decode($oldpayload->params)[2]->value);
      $this->assertEquals('TestAddProfile_65rdfcgvhu', json_decode($oldpayload->params)[0]->value);
    }

  }

  /** @test */
  public function it_can_delete_profiles()
  {
    Auth::loginUsingId(3);

    $profileToAdd = [
      'name' => 'TestAddDeleteProfile_78gydbhcfjijd',
      'operatingSystem' => 'Windows',
      'description' => 'This is a test profile.',
      'devices' => [],
      'users' => [],
      'tags' => [],
      'datetime' => '12:00:00 07-02-2024',
      'payloadList' => [
        [
          'PayloadName' => 'General',
          'icon' => 'https://example.com/payload1-icon.png',
          'config' => [
            'show' => false,
          ],
          'Fields' => [
            [
              'id' => 1,
              'label' => 'Field 1',
              'field_id' => 'field1',
              'value' => 'TestAddDeleteProfile_78gydbhcfjijd',
              'input_type' => 'text',
              'description' => 'Description for Field 1',
            ],
            [
              'id' => 2,
              'label' => 'Field 2',
              'field_id' => 'field2',
              'value' => true,
              'input_type' => 'checkbox',
              'description' => 'Description for Field 2',
            ],
            [
              'id' => 3,
              'label' => 'Field 3',
              'field_id' => 'field3',
              'value' => 'This is a test profile.',
              'input_type' => 'text',
              'description' => 'Description for Field 3',
            ],
            // Add more fields as needed
          ],
        ],
        // Add more payloads as needed
      ]
    ];

    $responseAdd = $this->json('post', 'api/uem/profile/create',
      $profileToAdd
    );
    $responseAdd->assertStatus(200);

    $responseGet = $this->json('get', '/api/uem/profile/');
    $responseGet->assertStatus(200);

    $profiles = $responseGet->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    $idProfileToDelete = null;
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);

      if ($profile['profileDisplayName'] === 'TestAddDeleteProfile_78gydbhcfjijd') {
        $idProfileToDelete = $profile['id'];
        break;
      }
    }
    $this->assertIsNumeric($idProfileToDelete);

    //$idProfileToDelete = 5;

    $deleteResponse = $this->json('delete', '/api/uem/profile/'.$idProfileToDelete);
    $deleteResponse->assertStatus(200);

    $getResponse = $this->json('get', '/api/uem/profile/');
    $getResponse->assertStatus(200);

    $profiles = $getResponse->json()['payload']['items'];
    $this->assertNotEmpty($profiles);

    $profileDeleted = true;
    foreach ($profiles as $profile) {
      $this->assertArrayHasKey('profileDisplayName', $profile);
      $this->assertNotEmpty($profile['profileDisplayName']);

      if ($profile['profileDisplayName'] === 'TestAddDeleteProfile_78gydbhcfjijd') {
        $profileDeleted = false;
        break;
      }
    }
    $this->assertTrue($profileDeleted);

    // check profile changes (storico) in oldprofile and oldpayloads
    $oldprofile = DB::connection($this->databaseMDM)->table('oldprofiles')->where('profileId', '=', $idProfileToDelete)->first();
    $oldpayloads = DB::connection($this->databaseMDM)->table('oldpayloads')->where('profileId', '=', $idProfileToDelete)->get()->toArray();

    // aggiungere altri campi
    $this->assertNotEmpty($oldprofile->profileDisplayName);
    $this->assertNotEmpty($oldprofile->operatingSystem);
    $this->assertEquals("TestAddDeleteProfile_78gydbhcfjijd", $oldprofile->profileDisplayName);
    $this->assertEquals("Windows", $oldprofile->operatingSystem);

    foreach ($oldpayloads as $oldpayload) {
      $this->assertNotEmpty($oldpayload->payloadDisplayName);
      $this->assertEquals('General', $oldpayload->payloadDisplayName);
      $this->assertEquals('This is a test profile.', json_decode($oldpayload->params)[2]->value);
      $this->assertEquals('TestAddDeleteProfile_78gydbhcfjijd', json_decode($oldpayload->params)[0]->value);
    }
  }

  /** @test */
  public function it_can_get_profiles_structure_by_os()
  {
    Auth::loginUsingId(3);

    $operatingSystem = ["Apple", "Android", "Windows", "Mixed"];

    $profilesOs = [];

    foreach ($operatingSystem as $os){
      $response = $this->json('post', '/api/uem/profile/payloadList',[
        'osType' => $os
      ]);
      $response->assertStatus(200);

      $profilesOs[] = $response->json()['payload'];
    }

    $this->assertNotEmpty($profilesOs);

    // aggingere altri campi
    foreach ($profilesOs as $profiles){
      foreach ($profiles as $profile) {
        $this->assertArrayHasKey('PayloadName', $profile);
        $this->assertArrayHasKey('icon', $profile);
        $this->assertArrayHasKey('config', $profile);
        $this->assertArrayHasKey('Fields', $profile);
        $this->assertNotEmpty($profile['PayloadName']);
        $this->assertNotEmpty($profile['icon']);
        $this->assertNotEmpty($profile['config']);
        // payloads
        $this->assertNotEmpty($profile['Fields']);
        foreach ($profile['Fields'] as $payload) {
          $this->assertArrayHasKey('id', $payload);
          $this->assertArrayHasKey('os', $payload);
          $this->assertArrayHasKey('label', $payload);
          $this->assertArrayHasKey('field_id', $payload);
          $this->assertArrayHasKey('value', $payload);
          $this->assertArrayHasKey('input_type', $payload);
          $this->assertArrayHasKey('description', $payload);

          $this->assertNotEmpty($payload['id'], );
          $this->assertNotEmpty($payload['os']);
          $this->assertNotEmpty($payload['label']);
          //$this->assertNotEmpty($payload['field_id']);
          //$this->assertNotEmpty($payload['value']);
          $this->assertNotEmpty($payload['input_type']);
          //$this->assertNotEmpty($payload['description']);
        }
      }
    }

  }
}
