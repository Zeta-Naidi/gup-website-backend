<?php

namespace App\Security;

use App\Exceptions\CatchedExceptionHandler;
use App\Jobs\LogAccess;
use Google\Type\Date;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlockedHostsHandler
{
  public static function blockUser($usernameToBlock, $reason = 'GENERIC')
  {
    try {
      $usernameAlreadyPresent = DB::table('ip_users_blocked')->where('username', $usernameToBlock)->first();
      if (!$usernameAlreadyPresent) {
        $inserted = DB::table('ip_users_blocked')->insert([
          "username" => $usernameToBlock,
          "blockedUntil" => new \DateTime('now +2 hours'),
          "blockedAt" => new \DateTime(),
        ]);
        if ($inserted) {
          $usernamesToBlock = DB::table('ip_users_blocked')
            ->where("blockedUntil", '>', new \DateTime())
            ->whereNotNull("username")
            ->get(['username', 'blockedUntil'])->toArray();
          Cache::put('usersBlocked', $usernamesToBlock);
          dispatch(new LogAccess(
            from: [
              "username" => null,
              "ip" => app()->request->ip(),
              "userAgent" => app()->request->userAgent()
            ],
            to: null,
            type: "USER_BLOCKED",
            value: ['usernameBlocked' => $usernamesToBlock, 'reason' => $reason],
            distributorId: null,
            timestamp: new \DateTime()
          ));
          return true;
        } else return false;
      } else return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR'], 500);
    }
  }

  public static function blockIp($ipToBlock, $reason = 'GENERIC'): bool|Response
  {
    try {
      $ipAlreadyPresent = DB::table('ip_users_blocked')->where('ip', $ipToBlock)->first();
      if (!$ipAlreadyPresent) {
        $inserted = DB::table('ip_users_blocked')->insert([
          "ip" => $ipToBlock,
          "blockedUntil" => new \DateTime('now +2 hours'),
          "blockedAt" => new \DateTime(),
        ]);
        if ($inserted) {
          $ipsToBeBlocked = DB::table('ip_users_blocked')
            ->where("blockedUntil", '>', new \DateTime())
            ->whereNotNull("ip")
            ->get(['ip', 'blockedUntil'])->toArray();
          Cache::put('ipsBlocked', $ipsToBeBlocked);
          dispatch(new LogAccess(
            from: [
              "username" => null,
              "ip" => app()->request->ip(),
              "userAgent" => app()->request->userAgent()
            ],
            to: null,
            type: "IP_ADDRESS_BLOCKED",
            value: ['ipBlocked'=> $ipToBlock, 'reason' => $reason],
            distributorId: null,
            timestamp: new \DateTime()
          ));
          return true;
        } else return false;
      } else return true;
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return response(['message' => 'GENERIC_ERROR'], 500);
    }
  }

  public static function checkIfIpIsBlocked(string $ip): bool
  {
    if (Cache::has('ipsBlocked')) {
      $ipsBlocked = Cache::get('ipsBlocked');
      foreach ($ipsBlocked as $ipBlocked) {
        if ($ipBlocked->ip == $ip)
          return true;
      }
      return false;
    } else {
      return (bool)DB::table('ip_users_blocked')->where('ip', $ip)->first();
    }
  }

  public static function checkIfUserIsBlocked(string $username): bool
  {
    if (Cache::has('usersBlocked')) {
      $usersBlocked = Cache::get('usersBlocked');
      foreach ($usersBlocked as $userBlocked) {
        if ($userBlocked->username == $username)
          return true;
      }
      return false;
    } else {
      return (bool)DB::table('ip_users_blocked')->where('username', $username)->first();
    }
  }

  public static function unBlockIp($ip): bool
  {
    try {
      $ipsBlocked = Cache::get('ipsBlocked');
      $ipsStillBlocked = [];
      foreach ($ipsBlocked as $ipBlocked) {
        if ($ipBlocked->ip != $ip)
          $ipsStillBlocked[] = $ipBlocked;
      }
      DB::table('ip_users_blocked')->where('ip', $ip)->delete();
      Cache::put('ipsBlocked', $ipsStillBlocked);
      return true;
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return false;
    }
  }

  public static function unBlockUser($username): bool
  {
    try {
      $usersBlocked = Cache::get('usersBlocked');
      $usersStillBlocked = [];
      foreach ($usersBlocked as $userBlocked) {
        if ($userBlocked->username != $username)
          $usersStillBlocked[] = $userBlocked;
      }
      DB::table('ip_users_blocked')->where('username', $username)->delete();
      Cache::put('usersBlocked', $usersStillBlocked);
      return true;
    } catch (\Exception $e) {
      CatchedExceptionHandler::handle($e);
      return false;
    }
  }
}
