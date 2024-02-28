<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('resellers', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('chimpaResellerId')->unique();
      $table->string('name', 255);
    });
    Schema::create('clients', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('chimpaClientId');
      $table->unsignedBigInteger('resellerId')->nullable();
      $table->string('baseUrl', 255);
      $table->string('host', 255);
      $table->string('companyName', 255);
      $table->timestamp('devicesLastUpdate')->nullable();
      $table->timestamp('eventsLastUpdate')->nullable();
      $table->timestamp('appUsagesLastUpdate')->nullable();
      $table->timestamp('networkActivitiesLastUpdate')->nullable();
      $table->string('lat', 255)->nullable();
      $table->string('lon', 255)->nullable();
      $table->string('countryCode', 10)->nullable();
      $table->string('phone', 50)->nullable();
      $table->string('email', 50)->nullable();
      $table->timestamps();
      $table->softDeletes();
      $table->index('chimpaClientId');
      $table->foreign('resellerId')->references('id')->on('resellers');
    });
    Schema::create('event_types', function (Blueprint $table) {
      $table->id();
      $table->unsignedSmallInteger('value')->unique();
      $table->string('key', 191)->unique();
    });
    Schema::create('devices', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('clientId');
      $table->string('serialNumber', 191)->unique();
      $table->string('name', 255)->nullable();
      $table->string('osType', 191)->nullable();
      $table->string('osVersion', 191)->nullable();
      $table->index(['osType','osVersion']);
      $table->index('osType');
      $table->foreign('clientId')->references('id')->on('clients');
    });
    Schema::create('app_usages', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('clientId');
      $table->string('deviceSerialNumber', 191);
      $table->string('packageName', 255)->nullable();
      $table->unsignedInteger('usageTime')->nullable();
      $table->timestamp('firstTimestamp')->nullable();
      $table->timestamp('lastTimestamp')->nullable();
      $table->foreign('deviceSerialNumber')->references('serialNumber')->on('devices');
      $table->foreign('clientId')->references('id')->on('clients');
    });
    Schema::create('network_activities', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('clientId');
      $table->string('deviceSerialNumber', 191);
      $table->string('packageName', 255)->nullable();
      $table->unsignedInteger('bytesIn')->nullable();
      $table->unsignedInteger('bytesOut')->nullable();
      $table->timestamp('firstTimestamp')->nullable();
      $table->timestamp('lastTimestamp')->nullable();
      $table->foreign('deviceSerialNumber')->references('serialNumber')->on('devices');
      $table->foreign('clientId')->references('id')->on('clients');
    });
    Schema::create('events', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('chimpaEventId')->unique();
      $table->unsignedBigInteger('clientId');
      $table->string('deviceSerialNumber', 191);
      $table->unsignedSmallInteger('type');
      $table->float('score');
      $table->string('criticalityLevel',32);
      $table->timestamp('detectionDate');
      $table->timestamp('updatedAt')->nullable();
      $table->string('description', 2048);
      $table->longText('docs');
      $table->unsignedSmallInteger('remediationType')->nullable();
      $table->string('remediationAction', 191)->nullable();
      $table->boolean('remediationActionStarted')->default(0);
      $table->boolean('hasBeenSolved')->default(0);
      $table->index('criticalityLevel');
      $table->index('chimpaEventId');
      $table->foreign('clientId')->references('id')->on('clients');
      $table->foreign('deviceSerialNumber')->references('serialNumber')->on('devices');
      $table->foreign('type')->references('value')->on('event_types');
    });
    DB::statement('ALTER TABLE events ADD INDEX detectionDate_index (detectionDate DESC)');
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('events');
    Schema::dropIfExists('network_activities');
    Schema::dropIfExists('app_usages');
    Schema::dropIfExists('devices');
    Schema::dropIfExists('event_types');
    Schema::dropIfExists('clients');
    Schema::dropIfExists('resellers');
  }
};
