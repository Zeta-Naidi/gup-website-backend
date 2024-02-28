<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('user_devices', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('userId')->index();
      $table->string('deviceId', 191)->index();
      $table->string('oldDeviceId', 191)->nullable()->index();
      $table->string('userAgent', 1024);
      $table->ipAddress('ipAddress');
      $table->timestamp('lastAccess');
      $table->index(['userId', 'deviceId']);
    });

    Schema::table('users', function (Blueprint $table) {
      $table->string('encryptionKey', 1024)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('encryptionKey');
    });
    Schema::dropIfExists('user_devices');
  }
};
