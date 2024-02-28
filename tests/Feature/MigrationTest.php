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
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MigrationTest extends TestCase
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
  public function it_can_migrate_up_and_down_mdm_database(){
    // Run the migration
    $this->artisan('migrate:fresh', [
      '--database' => $this->databaseMDM,
      '--path' => $this->databaseMdmMigrationPath,
    ]);

    // Define the expected table names
    $expectedTableNames = ['tags', 'tags_devices', 'tags_users', 'devices', 'devicesdetails', 'oldpayloads', 'payloads', 'profiles'];

    // Assert that each expected table exists
    foreach ($expectedTableNames as $tableName) {
      $this->assertTrue(Schema::connection($this->databaseMDM)->hasTable($tableName));
    }

    // Rollback the migration
    $this->artisan('migrate:rollback', [
      '--database' => $this->databaseMDM,
      '--path' => $this->databaseMdmMigrationPath,
      '--step' => 1,
    ]);

    // Assert that each expected table does not exist after rollback
    foreach ($expectedTableNames as $tableName) {
      $this->assertFalse(Schema::hasTable($tableName));
    }
  }
}
