<?php

namespace App\Dtos\Controller;

class ControllerResponse
{
  public bool $success;
  public mixed $payload;
  public ?int $httpStatus;
  public function __construct(bool $success, $payload = null, ?int $httpStatus = 200){
    $this->success = $success;
    $this->payload = $payload;
    $this->httpStatus = $httpStatus;
  }
}
