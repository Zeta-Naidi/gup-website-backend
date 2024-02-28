<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UemDeviceDetails extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'devicesdetails';

    protected $fillable = [
        "deviceId",
        "parentDeviceId",
        "hardwareDetails",
        "technicalDetails",
        "restrictions",
        "locationDetails",
        "networkDetails",
        "accountDetails",
        "osDetails",
        "securityDetails",
        "androidConfigs",
        "appleConfigs",
        "installedApps",
        "miscellaneous",
    ];

    public function getDecodedAttributes()
    {
        $decodedAttributes = [];
        foreach ($this->attributes as $key => $value) {
            $decodedAttributes[$key] = $this->isJson($value) ? json_decode($value, true) : $value;
        }
        return $decodedAttributes;
    }

    protected function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function device()
    {
        return $this->belongsTo(UemDevice::class, 'deviceId', 'id');
    }
}
