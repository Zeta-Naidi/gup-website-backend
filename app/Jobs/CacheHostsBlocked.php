<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheHostsBlocked implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try {
        $ipsToBeBlocked = DB::table('ip_users_blocked')
          ->where("blockedUntil", '>', new \DateTime())
          ->whereNotNull("ip")
          ->get(['ip', 'blockedUntil'])->toArray();
        Cache::put('ipsBlocked', $ipsToBeBlocked);

        $usernamesToBlock = DB::table('ip_users_blocked')
          ->where("blockedUntil", '>', new \DateTime())
          ->whereNotNull("username")
          ->get(['username', 'blockedUntil'])->toArray();
        Cache::put('usersBlocked', $usernamesToBlock);

        DB::table('ip_users_blocked')->where("blockedUntil", '<=', new \DateTime())->delete();

      } catch (\Exception $e) {
        \App\Exceptions\CatchedExceptionHandler::handle($e);
      }
    }
}
