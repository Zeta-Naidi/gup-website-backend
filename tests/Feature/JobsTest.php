<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobsTest extends TestCase
{
  /**
   * A basic test example.
   *
   * @return void
   */
  use RefreshDatabase;

  protected $userAuthenticated;

  public function artisan($command, $parameters = [])
  {
    if ($command === 'migrate' && !isset($parameters['--path'])) {
      $parameters['--path'] = 'database/migrations/usersDB/';
      $parameters['--database'] = 'sqlite_testing';
    }
    parent::artisan($command, $parameters);
  }

  protected function afterRefreshingDatabase()
  {
    $this->userAuthenticated = User::factory()->create();
    $this->artisan('make:database --testing ' . $this->userAuthenticated->nameDatabaseConnection);
    $this->_createConnectionInFile($this->userAuthenticated->nameDatabaseConnection);
    $this->_migrate($this->userAuthenticated->nameDatabaseConnection);

  }


  public function test_event_types_list()
  {
    Http::fake([
      'https://portal.chimpa.eu' => Http::response([
        "access_token" => "a9c31f872c73b7377c93f72b66845dc18f765085",
        "expires_in" => 3600,
        "token_type" => "Bearer",
        "scope" => "data.read",
      ]),
      'https:/*' => Http::response(['error' => 'fatal error'], 500),
    ]);
    $tags = Client::factory()->connection($this->userAuthenticated->nameDatabaseConnection)->count(20)->create();
    //dd($tags->toArray());
    //$job = new CollectClientData([$this->userAuthenticated->nameDatabaseConnection], 'EVENT_TYPE');
    $this->assertTrue(true);
    $this->_deleteTestConnectionFromConnectionsFile();
    //$job->handle();
  }

  public function test_to_clear_changes()
  {
    //$this->_deleteTestConnectionFromConnectionsFile();
    $this->assertTrue(true);

  }

  public function _createConnectionInFile($dbname)
  {
    $path = config_path() . '/database.php';
    $array = explode("\n", file_get_contents($path));
    $fp = fopen($path, 'r+');
    $key = array_search("  'connections' => [", $array);
    $data_to_write = [
      $key + 2 => "    '" . $dbname . "' => [",
      $key + 3 => "        'driver' => 'sqlite',",
      $key + 4 => "        'database' => ':memory:',",
      $key + 5 => "         'prefix' => '',",
      $key + 6 => "    ],",
      $key + 7 => " "
    ];
    $firstPart = array_slice($array, 0, $key + 1);
    $secondPart = array_slice($array, $key + 1);
    $wholePart = [...$firstPart, ...$data_to_write, ...$secondPart];
    $finale = implode("\n", $wholePart);
    fwrite($fp, $finale);
    fclose($fp);
  }

  public function _migrate($dbname)
  {
    \Artisan::call('config:cache');
    \Artisan::call('migrate --database=' . $dbname . ' --path=database/migrations/distributorsDB/');
    \Artisan::call('config:clear');
  }

  public function _deleteTestConnectionFromConnectionsFile()
  {
    $path = config_path() . '/database.php';
    $array = explode("\n", file_get_contents($path));
    $fp = fopen($path, 'r+');
    $key = array_search("    '" . $this->userAuthenticated->nameDatabaseConnection . "' => [", $array);
    if(is_numeric($key)){
      $firstPart = array_slice($array, 0, $key );
      $secondPart = array_slice($array, $key + 5);
      $wholePart = [...$firstPart, ...$secondPart];
      $finale = implode("\n", $wholePart);
      fwrite($fp, $finale);
      fclose($fp);
    }
  }
}
