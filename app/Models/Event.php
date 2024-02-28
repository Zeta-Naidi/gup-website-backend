<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
  public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "chimpaEventId",
        "clientId",
        "deviceSerialNumber",
        "type",
        "score",
        "detectionDate",
        "updatedAt",
        "description",
        "docs",
        "remediationType",
        "remediationAction",
        "remediationActionStarted",
        "hasBeenSolved",
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'urls' => 'array',
        'guides' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clientId', 'id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'deviceSerialNumber', 'serialNumber');
    }
  public function event_type()
  {
    return $this->belongsTo(EventType::class, 'type', 'value');
  }
}
