<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EnrollmentCodeHistory extends Authenticatable
{
    public $timestamps = false;
    protected $table = 'enrollmentcodeshistory';
    protected $fillable = ['type', 'value'];
}
