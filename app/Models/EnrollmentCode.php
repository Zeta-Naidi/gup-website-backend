<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class EnrollmentCode extends Authenticatable
{
    public $timestamps = false;
    protected $table = 'enrollmentCodes';
    protected $fillable = ['id', 'type', 'value'];
}
