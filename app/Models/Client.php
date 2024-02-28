<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
  use HasFactory, SoftDeletes;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $fillable = [
    "chimpaClientId",
    "baseUrl",
    "host",
    "companyName",
    "lat",
    "lon",
    "countryCode",
    'resellerId',
    "phone",
    "email"
  ];

  public function devices()
  {
    return $this->hasMany(Device::class, 'clientId', 'clientId');
  }

  public function events()
  {
    return $this->hasMany(Event::class, 'clientId', 'clientId');
  }
}
