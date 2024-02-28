<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkActivity extends Model
{
  use HasFactory;

  protected $table = 'network_activities';
  public $timestamps = false;
}
