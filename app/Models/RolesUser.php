<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesUser extends Model
{
  use HasFactory;

  protected $casts = [
    'clientsFilter' => 'json',
    'scoreFilter' => 'json',
    'eventTypeFilter' => 'json',
    'relationshipIds' => 'json'
  ];
}
