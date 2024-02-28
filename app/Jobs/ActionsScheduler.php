<?php

namespace App\Jobs;

use App\Repositories\ActionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class ActionsScheduler implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


  /**
   * Create a new job instance.
   */
  public function __construct()
  {

  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    try {
      /**
       * @var ActionRepository $repository
       */
      $repository = app(ActionRepository::class);
      $repository->setConnectionToDatabase(auth()->user()->nameDatabaseConnection);
      $actionsNotSent = $repository->getActionsNotSent()->formatControllerResponse();

      // per ogni action creo un instanza di Device (win, app, and), che richiama il metodo
      // sendPushNotification

      //TODO: send the push notification and set action attribute 'sentAt' to now()
      // 3 types of notification request :
      // - Apple (api.push.apple.com)
      // - Windows ()
      // - Google ()


      foreach ($actionsNotSent as $action) {
        // send the push notification

      }

      /*$responses = Http::pool(fn (Pool $pool) => [
        $pool->get('http://localhost/first'),
        $pool->get('http://localhost/second'),
        $pool->get('http://localhost/third'),
      ]);*/


    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }
  }
}
