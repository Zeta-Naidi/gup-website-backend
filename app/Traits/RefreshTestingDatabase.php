<?php

namespace App\Traits;

use Database\Seeders\TestingSeeder;
use Faker\Generator;
use Illuminate\Contracts\Console\Kernel;
use Faker\Factory as FakerFactory;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;

trait RefreshTestingDatabase
{
  /**
   * Define the path to the migrations for the trait.
   *
   * @param $database
   * @return string
   */
  protected function getMigrationPath($database): string
  {
    if($database === 'db_users_mssp_d3tGkTEST'){
      return 'database/migrations/usersDB';
    }
    elseif ($database === 'testing_mdm_prova1_d3tGkTEST') {
      return 'database/migrations/mdmDB';
    }else{
      return 'database/migrations/distributorsDB';
    }
  }

  /**
   * Refresh the testing databases.
   *
   * @param $database
   * @return void
   */
  protected function refreshDatabase($database): void
  {
    if (! RefreshDatabaseState::$migrated) {
      $this->artisan('migrate:fresh', [
        '--database' => $database,
        '--path' => $this->getMigrationPath($database),
      ]);

      $this->app[Kernel::class]->setArtisan(null);

      RefreshDatabaseState::$migrated = true;
    }
  }

  /**
   * Refresh and Seed the database with test data using Faker.
   *
   * @param $database
   * @param $seederClass
   * @return void
   */
  protected function refreshAndSeedDatabase($database, $seederClass): void
  {
    if (! RefreshDatabaseState::$migrated) {
      $this->artisan('migrate:fresh', [
        '--database' => 'db_users_mssp_d3tGkTEST',
        '--path' => 'database/migrations/usersDB',
      ]);

      $this->artisan('migrate:fresh', [
        '--database' => $database,
        '--path' => $this->getMigrationPath($database),
      ]);

      $this->artisan('seed:testing', [
        'database' => $database,
        'seederClass' => $seederClass,
      ]);

      $this->app[Kernel::class]->setArtisan(null);

      RefreshDatabaseState::$migrated = true;
    }
  }

  /**
   * Get an instance of Faker.
   *
   * @return Generator
   */
  protected function getFaker(): Generator
  {
    return FakerFactory::create();
  }
}
