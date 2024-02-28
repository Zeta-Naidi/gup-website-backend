<?php

namespace App\Utils;

class ControllerResponse
{
  public bool $success;
  public $payload;
  public ?int $httpStatus;
  public function __construct(bool $success, $payload, ?int $httpStatus = 200){
    $this->success = $success;
    $this->payload = $payload;
    $this->httpStatus = $httpStatus;
  }
}
