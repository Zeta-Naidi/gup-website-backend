<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UemDevice extends Model
{
  use HasFactory;

  public $timestamps = true;

  protected $table = 'devices';

  protected $casts = [
    'networkIdentity' => 'array',
    'configuration' => 'array',
    'deviceIdentity' => 'array',
  ];

  protected $fillable = [
    'deviceName',
    'parentDeviceId',
    'enrollmentType',
    "modelName",
    "macAddress",
    "meid",
    "osType",
    "osEdition",
    "osVersion",
    "udid",
    "vendorId",
    "osArchitecture",
    "abbinationCode",
    "mdmDeviceId",
    "manufacturer",
    "serialNumber",
    "imei",
    "isDeleted",
    "phoneNumber",
    "modelDevice",
    "productName",
    //json fields
    "networkIdentity",
    "configuration",
    "deviceIdentity"
  ];

  public static function find($deviceId)
  {
    return DB::connection('testing_mdm_prova_d3tGk')->table('devicesdetails')->where('deviceId', $deviceId,)->first();
  }

  public function deviceDetails(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->hasOne(UemDeviceDetails::class, 'deviceId', 'id');
  }
  /*public function client()
  {
      return $this->belongsTo(Client::class, 'clientId', 'id');
  }

  public function events()
  {
      return $this->hasMany(Event::class, 'deviceSerialNumber', 'serialNumber');
  }*/
}
