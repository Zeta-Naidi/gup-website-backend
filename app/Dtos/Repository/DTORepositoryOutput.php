<?php

namespace App\Dtos\Repository;

use App\Dtos\Controller\ControllerResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class DTORepositoryOutput
{
  private bool $success;
  private ?array $items;

  private object|int|null $singleItem;

  private ?float $executionTime;

  private ?int $statusCode;

  private ?array $additionalInfos;

  /**
   * @throws Exception
   */
  public function __construct(bool $success, array $items = null, object|int|null $singleItem = null, ?float $executionTime = null, ?int $statusCode = null, ?array $additionalInfos = null){
    $this->success = $success;
    $this->items = $items;
    $this->singleItem = $singleItem;
    $this->executionTime = $executionTime;
    $this->statusCode = $statusCode;
    $this->additionalInfos = $additionalInfos;
    if(!empty($this->items) && !empty($this->singleItem))
      throw new Exception('ONLY_ITEMS_OR_SINGLE_ITEM_CAN_BE_INSTANTIATED');
  }
  public function formatControllerResponse(): ControllerResponse
  {
    return new ControllerResponse($this->success, $this->formatPayload(), $this->statusCode);
  }

  private function formatPayload(): object|array|null
  {
    if(isset($this->additionalInfos)){
      return [
        ... $this->additionalInfos,
        'items' => $this->items ?? $this->singleItem
      ];
    }else{
      return $this->items ?? $this->singleItem;
    }
  }

  public function isSuccess(): bool
  {
    return $this->success;
  }

  public function getItems(): ?array
  {
    return $this->items;
  }

  public function getSingleItem(): object|int|null
  {
    return $this->singleItem;
  }

  public function getExecutionTime(): ?float
  {
    return $this->executionTime;
  }

}
