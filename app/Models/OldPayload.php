<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class OldPayload extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

    use HasFactory;
    public $timestamps = false;

    protected $table = 'oldpayloads';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
      'payloadUUID',
      'profileId',
      'payloadDisplayName',
      'payloadDescription',
      'payloadOrganization',
      'applePayloadType',
      'params',
      'payloadVersion',
      'profileVersion'
  ];
}
