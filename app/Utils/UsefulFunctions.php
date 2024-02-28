<?php

namespace App\Utils;

class UsefulFunctions
{
  public static function clearStringFromSpecialCharacters($stringToClear): string|null
  {
    try {
      //  (/[^\p{L}\p{N}\s]/u) matches any characters that are not letters (\p{L}), numbers (\p{N}), or whitespace (\s)
      $regex = '/[^\p{L}\p{N}\s]/u';
      return preg_replace($regex, '', $stringToClear);
    } catch (\Exception $e) {
      \App\Exceptions\CatchedExceptionHandler::handle($e);
      return null;
    }
  }

}
