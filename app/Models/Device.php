<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
  public $timestamps = false;

    protected $fillable = [
        "serialNumber",
        "name",
        "osType",
        "osVersion",
        "clientId",
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'deviceSerialNumber', 'serialNumber');
    }

}
