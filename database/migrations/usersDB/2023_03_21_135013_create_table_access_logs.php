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
    Schema::create('access_logs', function (Blueprint $table) {
      $table->id();
      $table->string('username',191)->nullable()->index();
      $table->ipAddress('ip')->index();
      $table->string('userAgent', 191)->index();
      $table->mediumText('toUser')->nullable();
      $table->string('type', 191)->index();
      $table->mediumText('value')->nullable();
      $table->unsignedInteger('distributorId')->nullable()->index();
      $table->timestamp('createdAt');
      $table->index(['distributorId', 'type']);
    });
    DB::statement('ALTER TABLE access_logs ADD INDEX createdAt_index (createdAt DESC)');
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('access_logs');
  }
};
