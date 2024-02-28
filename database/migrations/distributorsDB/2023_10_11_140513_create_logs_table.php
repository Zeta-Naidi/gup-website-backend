<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('logs', function (Blueprint $table) {
      $table->id();
      $table->string('username', 191)->nullable()->index();
      $table->ipAddress('ip')->index();
      $table->string('userAgent', 191)->index();
      $table->mediumText('toUser')->nullable();
      $table->string('type', 191)->index();
      $table->mediumText('value')->nullable();
      $table->json('clientIds')->nullable();
      $table->json('resellerIds')->nullable();
      $table->integer('clientIdsIndex')->index()->nullable();
      $table->integer('resellerIdsIndex')->index()->nullable();
      $table->smallInteger('logType')->index();
      $table->timestamp('createdAt');
    });
    DB::statement('ALTER TABLE logs ADD INDEX createdAt_index (createdAt DESC)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('logs');
  }
};
