<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    use HasFactory;
  public $timestamps = false;
  protected $table = 'resellers';

  protected $fillable = [
    'chimpaResellerId',
    'name',
  ];
}
