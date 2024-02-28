<?php

namespace App\Actions\Devices;

interface IActionSourceDevices
{

  /**
   *
   */
  public function actionData(array $device): array;

  /**
   * check compatibility with device/user
   */
  public function checkCompatibility(array $device, array $license): bool;
}
