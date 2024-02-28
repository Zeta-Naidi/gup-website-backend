<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

  protected $table = 'tags';

  protected $fillable = [
    "tagName",
    "createDate",
    "updateDate",
    "deleted_at"
  ];
}
