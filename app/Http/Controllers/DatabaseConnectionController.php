<?php

namespace App\Http\Controllers;

use App\Models\DatabaseConnection;
use Database\Seeders\MDMDatabaseSeeder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psy\Readline\Hoa\ConsoleOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Models\User;

class DatabaseConnectionController extends CrudController
{
  public function __construct()
  {
    $this->setModel('App\Models\DatabaseConnection');
    $this->_setDbConnection(config('database.default'));
  }

  /**
   * @param $attributes [
   * "distributor" => array containing info of new distributor
   * "connection" => optional param for connection configurations
   * ]
   * @return \http\Client\Response|void
   */
  public function create($attributes): bool|string
  {
    try {
      $distributor = $attributes["distributor"] ?? null;
      $connection = $attributes["connection"] ?? null;
      if (!$distributor)
        return false;
      $databaseName = (App::environment('production') ? 'production_' : 'testing_') . 'distributor_' . $distributor['user_id'] . '_d3tGk';
      if (config('database.unix_socket')) {
        if (env('DB_KEY')) {
          DatabaseConnection::create([
            "distributorName" => $distributor["companyName"],
            "driver" => "mysql",
            "host" => openssl_encrypt('127.0.0.1', 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "port" => openssl_encrypt('3306', 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "username" => openssl_encrypt(isset($connection) ? $connection["username"] : config('database.username'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "password" => openssl_encrypt(isset($connection) ? $connection["password"] : config('database.password'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "unix_socket" => openssl_encrypt(isset($connection) ? $connection["unixSocket"] : config('database.unix_socket'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "database" => $databaseName
          ]);
        } //SUPPORT OLD VERSIONS TODO REFACTOR OLD VERSIONS FOR BETTER SECURITY
        else {
          DatabaseConnection::create([
            "distributorName" => $distributor["companyName"],
            "driver" => "mysql",
            "host" => '127.0.0.1',
            "port" => '3306',
            "username" => isset($connection) ? $connection["username"] : config('database.username'),
            "password" => isset($connection) ? $connection["password"] : config('database.password'),
            "unix_socket" => config('database.unix_socket'),
            "database" => $databaseName
          ]);
        }
      } else {
        if (env('DB_KEY')) {
          DatabaseConnection::create([
            "distributorName" => $distributor["companyName"],
            "driver" => "mysql",
            "host" => openssl_encrypt(isset($connection) ? $connection["host"] : config('database.host'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "port" => openssl_encrypt(isset($connection) ? $connection["port"] : config('database.port'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "username" => openssl_encrypt(isset($connection) ? $connection["username"] : config('database.username'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "password" => openssl_encrypt(isset($connection) ? $connection["password"] : config('database.password'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
            "database" => $databaseName
          ]);
        } //SUPPORT OLD VERSIONS TODO REFACTOR OLD VERSIONS FOR BETTER SECURITY
        else {
          DatabaseConnection::create([
            "distributorName" => $distributor["companyName"],
            "driver" => "mysql",
            "host" => isset($connection) ? $connection["host"] : config('database.host'),
            "port" => isset($connection) ? $connection["port"] : config('database.port'),
            "username" => isset($connection) ? $connection["username"] : config('database.username'),
            "password" => isset($connection) ? $connection["password"] : config('database.password'),
            "database" => $databaseName
          ]);
        }
      }
      Artisan::call('make:database ' . $databaseName);

      return $databaseName;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function createMDM($instance = 'prova'): bool|string
  {
    try {
      $databaseName = (App::environment('production') ? 'production_' : 'testing_') . 'mdm_' . $instance . '_d3tGk';

      if (env('DB_KEY')) {
        DatabaseConnection::create([
          "distributorName" => $instance,
          "driver" => "mysql",
          "host" => openssl_encrypt(isset($connection) ? $connection["host"] : config('database.host'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
          "port" => openssl_encrypt(isset($connection) ? $connection["port"] : config('database.port'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
          "username" => openssl_encrypt(isset($connection) ? $connection["username"] : config('database.username'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
          "password" => openssl_encrypt(isset($connection) ? $connection["password"] : config('database.password'), 'aes-256-cbc', base64_decode(env('DB_KEY')), 0, env('DB_KEY_IV')),
          "database" => $databaseName
        ]);
      } //SUPPORT OLD VERSIONS TODO REFACTOR OLD VERSIONS FOR BETTER SECURITY
      else {
        DatabaseConnection::create([
          "distributorName" => $instance,
          "driver" => "mysql",
          "host" => isset($connection) ? $connection["host"] : config('database.host'),
          "port" => isset($connection) ? $connection["port"] : config('database.port'),
          "username" => isset($connection) ? $connection["username"] : config('database.username'),
          "password" => isset($connection) ? $connection["password"] : config('database.password'),
          "database" => $databaseName
        ]);
      }

      Artisan::call('make:database ' . $databaseName);

      return $databaseName;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function list($filters = [])
  {
    try {
      $query = $this->_getModel()::on($this->_getDbConnection());

      if(env('APP_MOD') == 'siem')
        $query = $query->where('type','distributor');

      return $query->get(['distributorName','database']);//Better not expose sensitive information
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }

  public function migrateDatabase($databaseName, $mod = 'up'): bool
  {
    try {
      if ($mod == 'up') {
        if ($databaseName === config('database.default'))
          Artisan::call("migrate --database=$databaseName --path=database/migrations/usersDB --force");
        else
          Artisan::call("migrate --database=$databaseName --path=database/migrations/distributorsDB --force");
      } else if ($mod == 'down') {
        if (App::environment('production')) {
          throw new \Exception('SHOULDNT_MIGRATE_DOWN_IN_PRODUCTION');
        }
        if ($databaseName === config('database.default'))
          Artisan::call("migrate:rollback --database=$databaseName --path=database/migrations/usersDB --force");
        else
          Artisan::call("migrate:rollback --database=$databaseName --path=database/migrations/distributorsDB --force");
      } else throw new \Exception('PARAMS_NOT_VALID');
      return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function migrateMDMDatabase($databaseName = 'testing_mdm_prova_d3tGk'): bool
  {
    try {
        Artisan::call("migrate --database=$databaseName --path=database/migrations/mdmDB --force");
        (new \Database\Seeders\MDMDatabaseSeeder)->run($databaseName);
      return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public function listChimpaDistributors()
  {
    try {
      $responseToken = Http::asForm()->post('https://portal.chimpa.eu/services/api/v20/mssp_token', [
        'grant_type' => 'client_credentials',
        'client_id' => config('app.client_id'),
        'client_secret' => config('app.client_secret'),
        'scope' => 'sensitive.data.read',
      ]);
      if (!($responseToken instanceof Response) || !$responseToken->ok()) {
        return ["success" => false, "message" => 'SERVER_CHIMPA_ERROR'];
      }
      $distributors = Http::withToken($responseToken->json()['access_token'])->get('https://portal.chimpa.eu/services/api/v20/mssp_distributors');
      if (($responseToken instanceof Response) && $distributors->ok())
        return ["success" => true, "payload" => $distributors->json()];
      else
        return ["success" => false, "message" => 'SERVER_CHIMPA_ERROR'];

    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['success' => false, 'message' => 'GENERIC_ERROR'], 500);
    }
  }
}
