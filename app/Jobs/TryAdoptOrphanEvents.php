<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TryAdoptOrphanEvents implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  private $serialNumbers;
  private $eventType;
  private $dbConnection;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($dbConnection, $serialNumbers = null, $eventType = null)
  {
    $this->serialNumbers = $serialNumbers;
    $this->eventType = $eventType;
    $this->dbConnection = $dbConnection;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      $eventsToAdopt = [];
      if (isset($this->serialNumbers)) {
        $eventsToAdopt = DB::connection($this->dbConnection)
          ->table('events_not_stored_successfully')
          ->whereIn('deviceSerialNumber', $this->serialNumbers)
          ->get();
      } else if (isset($this->eventType)) {
        $eventsToAdopt = DB::connection($this->dbConnection)
          ->table('events_not_stored_successfully')
          ->where('type', $this->eventType)
          ->get();
      }
      foreach ($eventsToAdopt as $newEvent) {
        $isEventPresent = Event::on($this->dbConnection)
          ->where('chimpaEventId', $newEvent->chimpaEventId)
          ->where('clientId', $newEvent->clientId)
          ->first();
        if (!$isEventPresent) {
          try {
            $resultInsert = Event::on($this->dbConnection)->insert([
              "chimpaEventId" => $newEvent->chimpaEventId,
              "clientId" => $newEvent->clientId,
              "deviceSerialNumber" => $newEvent->deviceSerialNumber,
              "type" => $newEvent->type,
              "score" => $newEvent->score,
              "updatedAt" => $newEvent->updatedAt,
              "criticalityLevel" => $newEvent->criticalityLevel,
              "detectionDate" => $newEvent->detectionDate,
              "description" => $newEvent->description,
              "docs" => $newEvent->docs,
              "remediationType" => $newEvent->remediationType,
              "remediationAction" => $newEvent->remediationAction,
              "remediationActionStarted" => $newEvent->remediationActionStarted,
              "hasBeenSolved" => $newEvent->hasBeenSolved,
              "subject" => $newEvent->subject
            ]);
            if ($resultInsert)
              DB::connection($this->dbConnection)
                ->table('events_not_stored_successfully')->where('id', $newEvent->id)->delete();
          } catch (\Exception $e) {
            // possible event with serial found but event type not yet added, very rare case but still...
            \App\Exceptions\CatchedExceptionHandler::handle($e);
          }
        } else {
          if (new \DateTime($newEvent->updatedAt) >= new \DateTime($isEventPresent->updatedAt)) {
            try {
              $resultUpdate = Event::on($this->dbConnection)
                ->where('chimpaEventId', $newEvent->chimpaEventId)
                ->where('clientId', $newEvent->clientId)
                ->update([
                  "chimpaEventId" => $newEvent->chimpaEventId,
                  "clientId" => $newEvent->clientId,
                  "deviceSerialNumber" => $newEvent->deviceSerialNumber,
                  "type" => $newEvent->type,
                  "score" => $newEvent->score,
                  "updatedAt" => $newEvent->updatedAt,
                  "criticalityLevel" => $newEvent->criticalityLevel,
                  "detectionDate" => $newEvent->detectionDate,
                  "description" => $newEvent->description,
                  "docs" => $newEvent->docs,
                  "remediationType" => $newEvent->remediationType,
                  "remediationAction" => $newEvent->remediationAction,
                  "remediationActionStarted" => $newEvent->remediationActionStarted,
                  "hasBeenSolved" => $newEvent->hasBeenSolved,
                  "subject" => $newEvent->subject
                ]);
              if ($resultUpdate)
                DB::connection($this->dbConnection)
                  ->table('events_not_stored_successfully')->where('id', $newEvent->id)->delete();
            } catch (\Exception $e) {
              // possible event with serial found but event type not yet added, very rare case but still...
              \App\Exceptions\CatchedExceptionHandler::handle($e);
            }
          } else
            DB::connection($this->dbConnection)
              ->table('events_not_stored_successfully')->where('id', $newEvent->id)->delete();
        }
      }
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
    }

  }
}
