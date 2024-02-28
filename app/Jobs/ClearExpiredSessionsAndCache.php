<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ClearExpiredSessionsAndCache implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {

  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      $expireDate = strtotime("-2 hours 5 minutes");
      $sessionsWithUserToDelete = DB::table('sessions')
        ->select(["sessions.id","user_id", "ip_address", "user_agent", "last_activity", "distributor_id", "username"])
        ->join('users', 'users.id', '=', 'sessions.user_id')
        ->where('last_activity', '<', $expireDate)
        ->get();
      DB::table('sessions')
        ->where('last_activity', '<', $expireDate)
        ->delete();
      foreach ($sessionsWithUserToDelete as $sessionExpired) {
        dispatch(
          new LogAccess(
            from: [
              "username" => $sessionExpired->username,
              "ip" => $sessionExpired->ip_address,
              "userAgent" => $sessionExpired->user_agent
            ],
            to: null, type: "SESSION_EXPIRED", value: null,
            distributorId: $sessionExpired->distributor_id,
            timestamp: new \DateTime(date('Y-m-d H:i:s', strtotime('+2 hours', $sessionExpired->last_activity)))
          )
        );
      }

      DB::table('cache')
        ->where('expiration', '<', strtotime('-5 minutes'))
        ->delete();
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }
}
