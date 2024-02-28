<?php

use App\Dtos\DatabaseLayer\DTODatabaseCreateInput;
use App\Dtos\DatabaseLayer\DTODatabaseDeleteInput;
use App\Dtos\DatabaseLayer\DTODatabaseSelectInput;
use App\Dtos\DatabaseLayer\DTODatabaseUpdateInput;
use App\Dtos\Repository\DTORepositoryOutput;
use App\Repositories\TagRepository;
use App\Services\DatabaseDataRetriever;
use App\Traits\RefreshTestingDatabase;
use Database\Seeders\TestingSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
  use RefreshTestingDatabase;

  private string $databaseMDM = "testing_mdm_prova1_d3tGkTEST";
  private string $databaseMdmMigrationPath = "database/migrations/mdmDB";
  private string $databaseMssp = "db_users_mssp_d3tGkTEST";


  public function setUp(): void
  {
    parent::setUp();
    // Refresh the $databaseMDM and seed it with fake data
    $this->refreshAndSeedDatabase($this->databaseMDM, TestingSeeder::class);
  }

  /** @test */
  public function it_can_get_tags()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/tag');
    $response->assertStatus(200);

    $tags = $response->json()['payload'];
    $this->assertNotEmpty($tags);

    foreach ($tags as $tag) {
      $this->assertArrayHasKey('tagName', $tag);
      $this->assertNotEmpty($tag['tagName']);
    }
  }

  /** @test */
  public function it_can_get_tags_with_pagination()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', 'api/tag',
      [
        'rowsPerPage' => 10,
        'page' => 1
      ]
    );
    $response->assertStatus(200);

    $tags = $response->json()['payload']['data'];
    $this->assertNotEmpty($tags);

    // Check pagination keys
    $payload = $response->json()['payload'];
    $paginationKeys = ['current_page', 'data', 'first_page_url', 'from', 'last_page', 'last_page_url', 'links', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'total'];
    foreach ($paginationKeys as $key) {
      $this->assertArrayHasKey($key, $payload);
    }
    $this->assertCount(10, $tags);

    foreach ($tags as $tag) {
      $this->assertArrayHasKey('tagName', $tag);
      $this->assertNotEmpty($tag['tagName']);
    }
  }

  /** @test */
  public function it_can_add_a_tag()
  {
    Auth::loginUsingId(1);

    $responseAdd = $this->json('post', 'api/tag/add', [
      'tagName' => 'TestAddTag',
    ]);
    $responseAdd->assertStatus(200);

    $responseGet = $this->json('get', 'api/tag');
    $responseGet->assertStatus(200);

    $tags = $responseGet->json()['payload'];
    $this->assertNotEmpty($tags);

    $tagFound = false;
    foreach ($tags as $tag) {
      $this->assertArrayHasKey('tagName', $tag);
      $this->assertNotEmpty($tag['tagName']);

      // Check if the added tag is found
      if ($tag['tagName'] === 'TestAddTag') {
        $tagFound = true;
        break;
      }
    }

    $this->assertTrue($tagFound);
  }

  /** @test */
  public function it_can_update_a_tag()
  {
    Auth::loginUsingId(1);

    // Add a tag
    $addResponse = $this->json('post', 'api/tag/add', [
      'tagName' => 'TagToUpdate',
    ]);
    $addResponse->assertStatus(200);

    // Update the tag
    $updateResponse = $this->json('put', '/api/tag/update/2', [
      'tagName' => 'UpdatedTagName', // id=2 because there is already a tag by default
    ]);
    $updateResponse->assertStatus(200);

    // Retrieve the updated tag
    $responseGet = $this->json('get', 'api/tag');
    $responseGet->assertStatus(200);

    $tags = $responseGet->json()['payload'];
    $this->assertNotEmpty($tags);

    $tagFound = false;
    foreach ($tags as $tag) {
      $this->assertArrayHasKey('tagName', $tag);
      $this->assertNotEmpty($tag['tagName']);

      // Check if the updated tag is found
      if ($tag['tagName'] === 'UpdatedTagName') {
        $tagFound = true;
        break;
      }
    }

    // Check if the tagName is updated
    $this->assertTrue($tagFound);
  }

  /** @test */
  public function it_can_delete_a_tag()
  {
    Auth::loginUsingId(1);

    // Delete the tag
    $deleteResponse = $this->json('delete', '/api/tag/delete/2');
    $deleteResponse->assertStatus(200); // id=2 because there is already a tag by default

    // Retrieve all tags after deletion
    $getResponse = $this->json('get', '/api/tag');
    $getResponse->assertStatus(200);

    // Check if the deleted tag is not present
    $tags = $getResponse->json()['payload'];
    $this->assertNotEmpty($tags);

    $tagDeleted = true;
    foreach ($tags as $tag) {
      $this->assertArrayHasKey('tagName', $tag);
      $this->assertNotEmpty($tag['tagName']);

      // Check if the deleted tag is still present
      if ($tag['tagName'] === 'TagToDelete') {
        $tagDeleted = false;
        break;
      }
    }

    // Assert that the tag is deleted
    $this->assertTrue($tagDeleted);
  }

  /** @test */
  public function it_can_get_tag_association_devices()
  {
    Auth::loginUsingId(1);

    $response = $this->json('get', '/api/uem/device/',
      [
      'rowsPerPage' => 10,
      'page' => 0
      ]
    );
    $response->assertStatus(200);

    $devices = $response->json()['payload']['data']; // pagination has also data and not only paylaod response structure
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
  public function it_can_get_tag_association_users()
  {
    // CHANGE AND TEST WITH PAGINATION
    Auth::loginUsingId(1);

    $response = $this->json('get', '/api/uem/device/',
      [
        'rowsPerPage' => 10,
        'page' => 0
      ]
    );
    $response->assertStatus(200);

    $users = $response->json()['payload']['data'];
    $this->assertNotEmpty($users);

    // Check pagination keys
    $payload = $response->json()['payload'];
    $paginationKeys = ['current_page', 'data', 'first_page_url', 'from', 'last_page', 'last_page_url', 'links', 'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'total'];
    foreach ($paginationKeys as $key) {
      $this->assertArrayHasKey($key, $payload);
    }
    $this->assertCount(10, $users);

    // aggingere altri campi
    foreach ($users as $user) {
      $this->assertArrayHasKey('parentDeviceId', $user);
      $this->assertArrayHasKey('deviceName', $user);
      $this->assertArrayHasKey('windowsAgentVersion', $user);
      $this->assertArrayHasKey('isOnline', $user);
      $this->assertNotEmpty($user['parentDeviceId']);
      $this->assertNotEmpty($user['deviceName']);
      $this->assertNotEmpty($user['windowsAgentVersion']);
      $this->assertIsNumeric($user['isOnline']);
    }
  }

  /** @test */
  public function it_can_associate_tags_with_devices_by_id()
  {
    Auth::loginUsingId(1);

    $addResponse = $this->post('/api/tag/associationByID/', [
      'type' => 'devices',
      'id' => 1
    ]);

    $addResponse->assertStatus(200);

    // Verify the structure of the response
    $responseData = $addResponse->json();
    $this->assertArrayHasKey('success', $responseData);
    $this->assertTrue($responseData['success']);

    $this->assertArrayHasKey('payload', $responseData);
    $payload = $responseData['payload'];

    // Assuming you expect an array with at least one element in the payload
    $this->assertIsArray($payload);
    $this->assertNotEmpty($payload);

    // Assuming each element in the payload is an array with an "id" field
    foreach ($payload as $item) {
      $this->assertArrayHasKey('id', $item);
      $this->assertIsNumeric($item['id']);
    }
  }

  /** @test */
  public function it_can_associate_tags_with_users_by_id()
  {
    Auth::loginUsingId(1);

    $addResponse = $this->post('/api/tag/associationByID/', [
      'type' => 'users',
      'id' => 1
    ]);

    $addResponse->assertStatus(200);

    // Verify the structure of the response
    $responseData = $addResponse->json();
    $this->assertArrayHasKey('success', $responseData);
    $this->assertTrue($responseData['success']);

    $this->assertArrayHasKey('payload', $responseData);
    $payload = $responseData['payload'];

    // Assuming you expect an array with at least one element in the payload
    $this->assertIsArray($payload);
    $this->assertNotEmpty($payload);

    // Assuming each element in the payload is an array with an "id" field
    foreach ($payload as $item) {
      $this->assertArrayHasKey('id', $item);
      $this->assertIsNumeric($item['id']);
    }
  }

  /** @test */
  public function it_can_update_tag_association_with_users_by_id()
  {
    Auth::loginUsingId(1);

    $updateResponse = $this->put('/api/tag/updateTagAssociationByID/', [
      'type' => 'users',
      'ids' => [1, 2],
      'tag_id' => 1,
    ]);

    // Assert that the response status is 200
    $updateResponse->assertStatus(200);

    // Verify the structure of the response
    $responseData = $updateResponse->json();
    $this->assertArrayHasKey('success', $responseData);
    $this->assertTrue($responseData['success']);

    $this->assertArrayHasKey('payload', $responseData);
    $payload = $responseData['payload'];

    // Assuming each element in the payload is an array with "user_id" field
    $userIdsInResponse = array_column($payload, 'user_id');

    // Check that the user IDs in the response correspond to the updated ones
    $this->assertEquals([1, 2], $userIdsInResponse);
  }

  /** @test */
  public function it_can_update_tag_association_with_devices_by_id()
  {
    Auth::loginUsingId(1);

    $updateResponse = $this->put('/api/tag/updateTagAssociationByID/', [
      'type' => 'devices',
      'ids' => [1, 2],
      'tag_id' => 1,
    ]);

    // Assert that the response status is 200
    $updateResponse->assertStatus(200);

    // Verify the structure of the response
    $responseData = $updateResponse->json();
    $this->assertArrayHasKey('success', $responseData);
    $this->assertTrue($responseData['success']);

    $this->assertArrayHasKey('payload', $responseData);
    $payload = $responseData['payload'];

    // Assuming each element in the payload is an array with "device_id" field
    $userIdsInResponse = array_column($payload, 'device_id');

    // Check that the user IDs in the response correspond to the updated ones
    $this->assertEquals([1, 2], $userIdsInResponse);
  }
}
