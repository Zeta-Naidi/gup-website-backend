<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('roles_users', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('userId')->unique();
      $table->longText('clientsFilter')->nullable();
      $table->longText('scoreFilter')->nullable();
      $table->longText('modFilter')->nullable();
      $table->longText('eventTypeFilter')->nullable();
      $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('roles_users');
  }
};
