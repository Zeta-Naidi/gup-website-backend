<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppUsage extends Model
{
  use HasFactory;
  public $timestamps = false;
  protected $table = 'app_usages';
}
