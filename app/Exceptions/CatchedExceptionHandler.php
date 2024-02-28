<?php

namespace App\Exceptions;

use App\Mail\ExceptionCatchedMail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CatchedExceptionHandler
{
  public static function handle($exception)
  {
    try {
      Log::error('CATCHED EXCEPTION, TRACE: ' . "\r" .
        implode(
          array_map(
            fn($row) => $row . "\r",
            array_slice(
              explode("#", $exception->getTraceAsString()), 0, 8)
          )
        ) . "\r\n".$exception->getMessage()."\r\n". $exception->getLine()."\r\n". $exception->getFile()
      );
/*      if (!(App::environment('local') || App::environment('staging'))) {
        $params = self::buildObject($exception);
        $paramsToMail = [];
        if (isset($params['description']))
          $paramsToMail['description'] = $params['description'];
        if (isset($params['line']))
          $paramsToMail['line'] = $params['line'];
        if (isset($params['backtrace']))
          $paramsToMail['backtrace'] = $params['backtrace'];
        if (isset($params['file']))
          $paramsToMail['file'] = $params['file'];
        $paramsToMail['host'] = config('app.url');
        Mail::to('monitor@xnoova.com')->send(new ExceptionCatchedMail($paramsToMail));
      }*/
    }
    catch (\Exception $e){
      Log::error("Error in CatchedExceptionHandel ". $e->getMessage());
    }
  }

  private static function buildObject($exception)
  {
    $objectBuild = [];
    $objectBuild['description'] = $exception->getMessage();
    $objectBuild['line'] = $exception->getLine();
    $objectBuild['file'] = $exception->getFile();
    $objectBuild['backtrace'] = explode("#", $exception->getTraceAsString());

    return $objectBuild;
  }
}
