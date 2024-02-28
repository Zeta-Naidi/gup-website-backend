<?php

namespace App\Devices;

use App\Dtos\Repository\DTORepositoryOutput;
use App\Entities\IEntity;

interface IWindowsMessage{
  public function toSyncMLString(int &$currentResponseCommandID):string;
}
