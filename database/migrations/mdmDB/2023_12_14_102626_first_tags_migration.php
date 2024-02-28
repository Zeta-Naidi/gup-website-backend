<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('tags', function (Blueprint $table) {
      $table->id();
      $table->string('tagName', 100);
    });

    Schema::create('tags_devices', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('tag_id');
      $table->unsignedBigInteger('device_id');

      $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
      $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
    });

    Schema::create('tags_users', function (Blueprint $table) {
      //TODO: foreign key - (users table)

      $table->id();
      $table->unsignedBigInteger('tag_id');
      $table->integer('user_id')->index();

      $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();

    // Drop the dependent tables first
    Schema::dropIfExists('tags_devices');
    Schema::dropIfExists('tags_users');

    // Now drop the main table
    Schema::dropIfExists('tags');

    Schema::enableForeignKeyConstraints();
  }
};
