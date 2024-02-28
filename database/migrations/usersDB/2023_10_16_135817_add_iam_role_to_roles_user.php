<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('roles_users', function (Blueprint $table) {
      $table->boolean('iamPermission')->default(false);
    });
  }
  public function down(): void
  {
    Schema::table('roles_users', function (Blueprint $table) {
      $table->dropColumn('iamPermission');
    });
  }
};
