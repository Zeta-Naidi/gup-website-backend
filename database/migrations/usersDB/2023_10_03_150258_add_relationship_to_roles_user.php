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
      $table->string('relationship')->default('distributor');
      $table->boolean('configurationPermission')->default(false);
      $table->boolean('accessLogsPermission')->default(false);
      $table->boolean('systemLogsPermission')->default(false);
      $table->text('relationshipIds')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('roles_users', function (Blueprint $table) {
      $table->dropColumn('relationshipIds');
      $table->dropColumn('systemLogsPermission');
      $table->dropColumn('accessLogsPermission');
      $table->dropColumn('configurationPermission');
      $table->dropColumn('relationship');
    });
  }
};
