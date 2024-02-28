<?php

namespace App\Dtos\DatabaseLayer;

use Illuminate\Support\Facades\Log;

class DTODatabaseOutput {
  // IMPORTANT The result of a query will be always an array, if the caller wants a single item, the repository should extract it from the array
  private array $result;

  private bool $success;
  private string $errorMessage;
  private float $executionTime;
  private int|null $statusCode;
  private array|null $additionalInfos;

  public function __construct(array $result, bool $success, float $executionTime, $statusCode = null, string $errorMessage = null, array $additionalInfos = null) {
    $this->result = $result;
    $this->success = $success;
    $this->errorMessage = $errorMessage ?? '';
    $this->executionTime = $executionTime;
    $this->statusCode = $statusCode ?? 0;
    $this->additionalInfos = $additionalInfos ?? null;
  }

  public function isSuccess(): bool
  {
    return $this->success;
  }

  public function getErrorMessage(): string
  {
    return $this->errorMessage;
  }

  public function getExecutionTime(): float
  {
    return $this->executionTime;
  }

  public function getStatusCode(): ?int
  {
    return $this->statusCode;
  }

  public function getPayload(): array{
    return $this->result;
  }

  public function getAdditionalInfos(): array|null{
    return $this->additionalInfos;
  }
}

