<?php

namespace App\Console\Commands;

use Database\Seeders\TestingSeeder;
use Illuminate\Console\Command;

class SeedTestingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:testing {database} {seederClass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the testing database with the specified seeder class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
      $database = $this->argument('database');
      $seederClass = $this->argument('seederClass');

      $this->info("Seeding {$database} database with {$seederClass} seeder...");

      // Instantiate the seeder and call the run method
      $seeder = new TestingSeeder();
      $seeder->run($database);

      $this->info("Seeder {$seederClass} completed for {$database} database.");

      return 0;
    }
}
