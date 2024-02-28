<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('access_logs', function (Blueprint $table) {
      $table->smallInteger('logType')->index();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('access_logs', function (Blueprint $table) {
      $table->dropColumn('logType');
    });
  }
};

