<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MDMDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run($database)
    {
      $profiles = [
        [
          'id' => 1,
          'profileDisplayName' => 'ORGANIZATION_PROFILE',
          'profileDescription' => NULL,
          'operatingSystem' => 'mixed',
          'profileType' => 'Configuration',
          'profileUUID' => 'FA00EBA4-FFC6-4BB6-9F81-F101D0A3336F',
          'profileExpirationDate' => NULL,
          'removalDate' => NULL,
          'durationUntilRemoval' => NULL,
          'durationUntilRemovalDate' => NULL,
          'consentText' => NULL,
          'profileRemovalDisallowed' => 0,
          'profileScope' => NULL,
          'profileOrganization' => NULL,
          'isEncrypted' => 0,
          'profileVersion' => 1,
          'onSingleDevice' => 0,
          'limitOnDates' => NULL,
          'limitOnWifiRange' => NULL,
          'limitOnPublicIps' => NULL,
          'home' => 0,
          'copeMaster' => 0,
          'enabled' => 1,
        ],
        [
          'id' => 2,
          'profileDisplayName' => 'TEACHERS_PROFILE',
          'profileDescription' => NULL,
          'operatingSystem' => 'mixed',
          'profileType' => 'Configuration',
          'profileUUID' => 'A6CAAAC9-8C95-4883-8A9B-38F2333FA28D',
          'profileExpirationDate' => NULL,
          'removalDate' => NULL,
          'durationUntilRemoval' => NULL,
          'durationUntilRemovalDate' => NULL,
          'consentText' => NULL,
          'profileRemovalDisallowed' => 0,
          'profileScope' => NULL,
          'profileOrganization' => NULL,
          'isEncrypted' => 0,
          'profileVersion' => 1,
          'onSingleDevice' => 0,
          'limitOnDates' => NULL,
          'limitOnWifiRange' => NULL,
          'limitOnPublicIps' => NULL,
          'home' => 0,
          'copeMaster' => 0,
          'enabled' => 1,
        ],
        [
          'id' => 3,
          'profileDisplayName' => 'STUDENTS_PROFILE',
          'profileDescription' => NULL,
          'operatingSystem' => 'mixed',
          'profileType' => 'Configuration',
          'profileUUID' => '22821B70-E240-40C5-8314-03936C945825',
          'profileExpirationDate' => NULL,
          'removalDate' => NULL,
          'durationUntilRemoval' => NULL,
          'durationUntilRemovalDate' => NULL,
          'consentText' => NULL,
          'profileRemovalDisallowed' => 0,
          'profileScope' => NULL,
          'profileOrganization' => NULL,
          'isEncrypted' => 0,
          'profileVersion' => 1,
          'onSingleDevice' => 0,
          'limitOnDates' => NULL,
          'limitOnWifiRange' => NULL,
          'limitOnPublicIps' => NULL,
          'home' => 0,
          'copeMaster' => 0,
          'enabled' => 1,
        ],
        [
          'id' => 4,
          'profileDisplayName' => 'CLASSROOM_PROFILE',
          'profileDescription' => NULL,
          'operatingSystem' => 'mixed',
          'profileType' => 'Configuration',
          'profileUUID' => 'F46988E4-087E-4D4C-B7A3-71B51F8FCEC5',
          'profileExpirationDate' => NULL,
          'removalDate' => NULL,
          'durationUntilRemoval' => NULL,
          'durationUntilRemovalDate' => NULL,
          'consentText' => NULL,
          'profileRemovalDisallowed' => 0,
          'profileScope' => NULL,
          'profileOrganization' => NULL,
          'isEncrypted' => 0,
          'profileVersion' => 1,
          'onSingleDevice' => 0,
          'limitOnDates' => NULL,
          'limitOnWifiRange' => NULL,
          'limitOnPublicIps' => NULL,
          'home' => 0,
          'copeMaster' => 0,
          'enabled' => 1,
        ],
      ];

      foreach ($profiles as $profile) {
        Profile::on($database)->create($profile);
      }
    }
    // TODO: add devices seeder (php artisan db:seed --class=DevicesSeeder --database=testing_mdm_prova_d3tGk)
}
