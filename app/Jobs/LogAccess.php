<?php

namespace App\Jobs;

use App\Models\RolesUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Exception;

class LogAccess implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $_validTypes = [
    //Access Logs
    "SUCCESS_LOGIN" => true,
    "LOGOUT" => true,
    "SESSION_EXPIRED" => true,
    "WRONG_LOGIN" => true,
    "SUCCESS_LOGIN_MFA" => true,
    "SUCCESS_REGISTER_MFA" => true,
    "WRONG_LOGIN_MFA" => true,
    "WRONG_REGISTER_MFA" => true,
    "SUCCESS_RESET_PASSWORD" => true,
    "WRONG_RESET_PASSWORD" => true,
    //System Logs
    "CREATE_USER" => true,
    "UPDATE_USER" => true,
    "DELETE_USER" => true,
    "CREATE_CONFIGURATION" => true,
    "UPDATE_CONFIGURATION" => true,
    "DELETE_CONFIGURATION" => true,
    "USER_BLOCKED" => true,
    "IP_ADDRESS_BLOCKED" => true,
  ];
  private array $from;
  private ?array $to;
  private string $type;
  private ?array $value;
  private ?int $distributorId;
  private \DateTime $timestamp;
  private ?RolesUser $rolesUser;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($from, $to, $type, $value, $distributorId, $timestamp, ?RolesUser $rolesUser = null)
  {
    $this->from = $from;
    $this->to = $to;
    $this->type = $type;
    $this->value = $value;
    $this->distributorId = $distributorId;
    $this->timestamp = $timestamp;
    $this->rolesUser = $rolesUser;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      if ($this->_validateParameters()) {
        if (!empty($this->distributorId)) {
          $nameConnection = App::environment('production') ? 'production_' : 'testing_';
          $nameConnection .= 'distributor_' . $this->distributorId . '_d3tGk';
          DB::connection($nameConnection)->table('logs')->insert(
            [
              "username" => $this->from["username"],
              "ip" => $this->from["ip"],
              "userAgent" => $this->from["userAgent"],
              "toUser" => isset($this->to) ? json_encode($this->to) : null,
              "type" => $this->type,
              "logType" => $this->_getLogType($this->type),
              "value" => isset($this->value) ? json_encode($this->value) : null,
              "createdAt" => $this->timestamp,
              "resellerIds" => $this->_getResellerIds($this->rolesUser),
              "clientIds" => $this->_getClientIds($this->rolesUser),
              "resellerIdsIndex" => $this->_getResellerIdsIndex($this->rolesUser),
              "clientIdsIndex" => $this->_getClientIdsIndex($this->rolesUser),
            ]);
        } else {
          DB::connection(config('database.default'))->table('access_logs')->insert(
            [
              "username" => $this->from["username"],
              "ip" => $this->from["ip"],
              "userAgent" => $this->from["userAgent"],
              "toUser" => isset($this->to) ? json_encode($this->to) : null,
              "type" => $this->type,
              "logType" => $this->_getLogType($this->type),
              "value" => isset($this->value) ? json_encode($this->value) : null,
              "distributorId" => null,
              "createdAt" => $this->timestamp,
            ]);
        }
      } else throw new Exception('PARAMETERS_IN_LOG_NOT_VALID');
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }

  private function _validateParameters(): bool
  {
    return isset($this->from) && (isset($this->type) && isset($this->_validTypes[$this->type]));
  }

  private function _getLogType($type): int
  {
    return [
      //Access Logs
      "SUCCESS_LOGIN" => 0,
      "LOGOUT" => 0,
      "SESSION_EXPIRED" => 0,
      "WRONG_LOGIN" => 0,
      "SUCCESS_LOGIN_MFA" => 0,
      "SUCCESS_REGISTER_MFA" => 0,
      "WRONG_LOGIN_MFA" => 0,
      "WRONG_REGISTER_MFA" => 0,
      "SUCCESS_RESET_PASSWORD" => 0,
      "WRONG_RESET_PASSWORD" => 0,
      //System Logs
      "CREATE_USER" => 1,
      "UPDATE_USER" => 1,
      "DELETE_USER" => 1,
      "CREATE_CONFIGURATION" => 1,
      "UPDATE_CONFIGURATION" => 1,
      "DELETE_CONFIGURATION" => 1,
      "USER_BLOCKED" => 1,
      "IP_ADDRESS_BLOCKED" => 1,
    ][$type];
  }

  private function _getResellerIds(RolesUser|null $rolesUser): null|string
  {
    if(!empty($rolesUser->relationshipIds) && $rolesUser->relationship == 'reseller')
      return json_encode($rolesUser->relationshipIds);
    else
      return null;
  }

  private function _getClientIds(RolesUser|null $rolesUser): null|string
  {
    if(!empty($rolesUser->relationshipIds) && $rolesUser->relationship == 'client')
      return json_encode($rolesUser->relationshipIds);
    else
      return null;
  }

  private function _getResellerIdsIndex(RolesUser|null $rolesUser): null|int
  {
    if(!empty($rolesUser) && $rolesUser->relationship == 'reseller')
      return array_reduce($rolesUser->relationshipIds, function($sum,$id) {
        $sum += (int)$id;
        return $sum;
      });
    else
      return null;
  }

  private function _getClientIdsIndex(RolesUser|null $rolesUser): null|int
  {
    if(!empty($rolesUser) && $rolesUser->relationship == 'client')
      return array_reduce($rolesUser->relationshipIds, function($sum,$id) {
        $sum += (int)$id;
        return $sum;
      });
    else
      return null;
  }
}
