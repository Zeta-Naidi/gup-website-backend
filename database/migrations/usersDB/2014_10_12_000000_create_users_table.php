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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->rememberToken();
      $table->string('nameDatabaseConnection', 255)->nullable();
      $table->string('password');
      $table->unsignedInteger('distributor_id')->nullable();
      $table->string('piva', 128)->nullable();
      $table->string('email', 128)->unique()->index();
      $table->string('username', 128)->unique()->index();
      $table->string('companyName', 255);
      $table->unsignedSmallInteger('levelAdmin');
      $table->timestamps();
    });

    Schema::create('cache', function ($table) {
      $table->string('key',191)->unique();
      $table->text('value');
      $table->integer('expiration');
    });

    Schema::create('sessions', function ($table) {
      $table->string('id', 191)->primary();
      $table->foreignId('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->text('payload');
      $table->integer('last_activity')->index();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('cache');
    Schema::dropIfExists('sessions');
  }
};
