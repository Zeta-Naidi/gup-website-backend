<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class Profile extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

    use HasFactory;
    public $timestamps = false;

    protected $table = 'profiles';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'id',
    'profileDisplayName',
    'profileDescription',
    'operatingSystem',
    'profileType',
    'profileUUID',
    'profileExpirationDate',
    'removalDate',
    'durationUntilRemoval',
    'durationUntilRemovalDate',
    'consentText',
    'profileRemovalDisallowed',
    'profileScope',
    'profileOrganization',
    'isEncrypted',
    'profileVersion',
    'onSingleDevice',
    'limitOnDates',
    'limitOnWifiRange',
    'limitOnPublicIps',
    'home',
    'copeMaster',
    'enabled',
  ];

    public function payloads()
    {
        return $this->hasMany(Payload::class, 'profileId');
    }

    public function oldPayloads()
    {
        return $this->hasMany(OldPayload::class, 'profileId');
    }


}
