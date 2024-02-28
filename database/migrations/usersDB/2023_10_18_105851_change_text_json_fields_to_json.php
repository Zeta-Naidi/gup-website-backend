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
    Schema::table('roles_users', function (Blueprint $table) {
      $table->json('relationshipIds')->change();
      $table->json('clientsFilter')->change();
      $table->json('scoreFilter')->change();
      $table->json('eventTypeFilter')->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('roles_users', function (Blueprint $table) {
      $table->text('relationshipIds')->change();
      $table->longText('clientsFilter')->change();
      $table->longText('scoreFilter')->change();
      $table->longText('eventTypeFilter')->change();
    });
  }
};
