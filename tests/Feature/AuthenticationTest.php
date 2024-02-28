<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatabaseController;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
  use RefreshDatabase;

  public function artisan($command, $parameters = [])
  {
    if ( $command === 'migrate' && !isset($parameters['--path'])) {
      $parameters['--path'] = 'database/migrations/usersDB/';
      $parameters['--database'] = 'sqlite_testing';
    }

    return parent::artisan($command, $parameters);
  }
  protected function refreshTestDatabase(): void
  {
    if (!RefreshDatabaseState::$migrated) {
      $this->artisan('migrate:fresh', $this->migrateFreshUsing());
      $this->app[Kernel::class]->setArtisan(null);

      RefreshDatabaseState::$migrated = true;
    }
    $this->beginDatabaseTransaction();
  }

  /**
   * A basic feature test example.
   *
   * @return void
   */
  public function test_login_handle_portal_not_responding()
  {
    Http::fake([
      'https://portal.chimpa.eu/services/api/*' => Http::response(['error' => 'fatal error'], 500),
    ]);
    $this->json('post', 'api/user/login', ["code" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841"])
      ->assertStatus(500);
  }

  public function test_login_handle_payload_not_correct_from_portal()
  {
    Http::fake([
      'https://portal.chimpa.eu/services/api/*' => Http::response(['bug' => 'no idea what i am doing']),
    ]);
    $this->json('post', 'api/user/login', ["code" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841"])
      ->assertJson(['message' => 'GENERIC_ERROR']);
  }

  public function test_login_handle_severChimpa_not_responding()
  {
    Http::fake([
      'https://portal.chimpa.eu/services/api/v20/mssp_token' => Http::response([
        "access_token" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841",
        "expires_in" => 3600,
        "token_type" => "bearer",
        "scope" => "data.read",
        "refresh_token" => "bbe3fc84dd09b3832b79c6231539a4d8ddc0bfe9",
      ]),
      'https://portal.chimpa.eu/services/api/v20/mssp_user_data' => Http::response(['error' => 'fatal error'], 500),
    ]);
    $this->json('post', 'api/user/login', ["code" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841"])
      ->assertJson(['message' => 'Server Cloud not giving user authenticated infos']);
  }

  public function test_login_handle_new_user()
  {
    Http::fake([
      'https://portal.chimpa.eu/services/api/v20/mssp_token' => Http::response([
        "access_token" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841",
        "expires_in" => 3600,
        "token_type" => "bearer",
        "scope" => "data.read",
        "refresh_token" => "bbe3fc84dd09b3832b79c6231539a4d8ddc0bfe9",
      ]),
      'https://portal.chimpa.eu/services/api/v20/mssp_user_data' => Http::response([
        "distributor_id" => "1",
        "companyName" => "Xnoova piccola",
        "username" => "example@example.com",
        "piva" => "IT3249723948"]),
    ]);
    $this->mock(DatabaseController::class, function (MockInterface $mock) {
      $mock->shouldReceive('create')->once();
    });
    $this->json('post', 'api/user/login', ["code" => "a"])->assertJsonStructure([
       'id',
       'username',
       'companyName',
       'created_at',
       'updated_at',
       'haveToMigrate',
     ]);
  }

  public function test_login_handle_user_already_registered()
  {

    $newUserSerialized = User::factory()->create()->toArray();

    Http::fake([
      'https://portal.chimpa.eu/services/api/v20/mssp_token' => Http::response([
        "access_token" => "a0b57383bf86c0b49cacf77dfb73329f1ea47841",
        "expires_in" => 3600,
        "token_type" => "bearer",
        "scope" => "data.read",
        "refresh_token" => "bbe3fc84dd09b3832b79c6231539a4d8ddc0bfe9",
      ]),
      'https://portal.chimpa.eu/services/api/v20/mssp_user_data' => Http::response([
        "distributor_id" => $newUserSerialized['distributor_id'],
        "companyName" => $newUserSerialized['companyName'],
        "username" => $newUserSerialized['username'],
        "piva" => $newUserSerialized['piva'],
      ]),
    ]);
    $this->json('post', 'api/user/login', ["code" => "a"])
      ->assertJsonStructure([
        'id',
        'username',
        'companyName',
        'created_at',
        'updated_at',
      ]);
  }

  //TODO logout test, problem with sanctum when testing logout
  /*public function test_logout()
  {
    Sanctum::actingAs(
      User::factory()->create()
    );
  }*/

}

