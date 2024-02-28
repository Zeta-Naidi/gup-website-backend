<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseConnection extends Model
{
    use HasFactory;

  protected $table = 'database_connections';
  public $timestamps = false;

  protected $fillable = [
    "distributorName",
    "driver",
    "port",
    "database",
    "username",
    "password",
    "unix_socket",
    'host',
  ];
}
