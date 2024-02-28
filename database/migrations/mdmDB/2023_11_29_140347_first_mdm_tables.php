<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Profile;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('profiles', function (Blueprint $table) {
      $table->id();
      $table->string('profileDisplayName', 64);
      $table->string('profileDescription', 255)->nullable();
      $table->string('profileType', 128);
      $table->string('profileUUID', 128);
      $table->string('operatingSystem', 128);
      $table->timestamp('profileExpirationDate')->nullable();
      $table->timestamp('removalDate')->nullable();
      $table->float('durationUntilRemoval')->nullable();
      $table->integer('durationUntilRemovalDate')->nullable();
      $table->string('consentText', 255)->nullable();
      $table->tinyInteger('profileRemovalDisallowed',);
      $table->string('profileScope', 255)->nullable();
      $table->string('profileOrganization', 255)->nullable();
      $table->tinyInteger('isEncrypted',);
      $table->integer('profileVersion');
      $table->integer('onSingleDevice');
      $table->text('limitOnDates')->nullable();
      $table->text('limitOnWifiRange')->nullable();
      $table->text('limitOnPublicIps')->nullable();
      $table->tinyInteger('home',);
      $table->tinyInteger('copeMaster',);
      $table->tinyInteger('enabled',);
      $table->timestamp('createdAt');
    });

      Schema::create('oldprofiles', function (Blueprint $table) {
        $table->id();
        $table->integer('profileId')->nullable();
        $table->string('profileDisplayName', 64);
        $table->string('profileDescription', 255)->nullable();
        $table->string('profileType', 128);
        $table->string('profileUUID', 128);
        $table->string('operatingSystem', 128);
        $table->timestamp('profileExpirationDate')->nullable();
        $table->timestamp('removalDate')->nullable();
        $table->float('durationUntilRemoval')->nullable();
        $table->integer('durationUntilRemovalDate')->nullable();
        $table->string('consentText', 255)->nullable();
        $table->tinyInteger('profileRemovalDisallowed',);
        $table->string('profileScope', 255)->nullable();
        $table->string('profileOrganization', 255)->nullable();
        $table->tinyInteger('isEncrypted',);
        $table->integer('profileVersion');
        $table->integer('onSingleDevice');
        $table->text('limitOnDates')->nullable();
        $table->text('limitOnWifiRange')->nullable();
        $table->text('limitOnPublicIps')->nullable();
        $table->tinyInteger('home',);
        $table->tinyInteger('copeMaster',);
        $table->tinyInteger('enabled',);
        $table->json('profileChanges')->nullable();
        $table->timestamp('createdAt');
      });

      Schema::create('payloads', function ($table) {
        $table->id();
        $table->string('payloadUUID', 128);
        $table->integer('profileId');
        $table->string('payloadDisplayName', 255);
        $table->string('payloadDescription', 255)->nullable();
        $table->string('payloadOrganization', 255)->nullable();
        $table->string('applePayloadType', 255);
        $table->json('params')->nullable();
        $table->json('config')->nullable();
        $table->integer('payloadVersion');
        $table->timestamp('createdAt');
    });

      Schema::create('oldPayloads', function ($table) {
        $table->id();
        $table->string('payloadUUID', 128);
        $table->integer('profileId');
        $table->string('payloadDisplayName', 255);
        $table->string('payloadDescription', 255)->nullable();
        $table->string('payloadOrganization', 255)->nullable();
        $table->string('applePayloadType', 255);
        $table->json('params')->nullable();
        $table->json('config')->nullable();
        $table->integer('payloadVersion');
        $table->integer('profileVersion')->nullable();
        $table->timestamp('createdAt');
    });

    Schema::create('devices', function (Blueprint $table) {
      // Primary Fields
      $table->id();
      $table->integer('parentDeviceId')->nullable();
      $table->string('deviceName', 200)->nullable();
      $table->string('modelName', 250)->nullable()->charset('utf8mb4');
      $table->integer('enrollmentType')->nullable();
      $table->string('macAddress', 50)->nullable()->charset('utf8mb4');
      $table->string('meid', 14)->nullable()->charset('utf8mb4');
      $table->string('osType', 10)->default('')->charset('utf8mb4');
      $table->string('osEdition', 20)->charset('utf8mb4');
      $table->string('osVersion', 50)->charset('utf8mb4');
      $table->string('udid', 100)->nullable()->charset('utf8mb4');
      $table->string('vendorId', 100)->nullable()->charset('utf8mb4');
      $table->string('osArchitecture', 100)->nullable();
      $table->string('abbinationCode', 100)->nullable();
      $table->integer('mdmDeviceId')->nullable();
      $table->string('manufacturer', 50);
      $table->string('serialNumber', 100)->nullable();
      $table->string('imei', 150)->nullable()->charset('utf8mb4');
      $table->tinyInteger('isDeleted')->default(0);
      $table->string('phoneNumber', 50)->nullable()->charset('utf8mb4');
      $table->tinyInteger('isOnline')->default(0);
      $table->string('brand', 250)->nullable()->charset('utf8mb4');
      $table->json('networkIdentity')->nullable();
      $table->json('configuration')->nullable();
      $table->json('deviceIdentity')->nullable();
      $table->timestamp('createdAt');
    });

    Schema::create('devicesDetails', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('deviceId');
      $table->foreign('deviceId')->references('id')->on('devices')->onDelete('cascade');
      $table->integer('parentDeviceId')->nullable();
      // Semantic JSON Fields
      $table->json('hardwareDetails')->nullable();
      $table->json('technicalDetails')->nullable();
      $table->json('restrictions')->nullable();
      $table->json('locationDetails')->nullable();
      $table->json('networkDetails')->nullable();
      $table->json('accountDetails')->nullable();
      $table->json('osDetails')->nullable();
      $table->json('securityDetails')->nullable();
      $table->json('androidConfigs')->nullable();
      $table->json('appleConfigs')->nullable();
      $table->json('installedApps')->nullable();
      $table->json('miscellaneous')->nullable();
    });
    Schema::create('devicesProfiles', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('deviceId');
      $table->foreign('deviceId')->references('id')->on('devices')->onDelete('cascade');
      $table->unsignedBigInteger('profileId');
      $table->foreign('profileId')->references('id')->on('profiles')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('profiles');
    Schema::dropIfExists('payloads');
    Schema::dropIfExists('oldPayloads');
    Schema::dropIfExists('devicesDetails');
    Schema::dropIfExists('devices');
    //
  }
};
