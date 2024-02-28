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
      Schema::table('resellers', function (Blueprint $table) {
        $table->string('email', 255)->nullable();
      });
      Schema::table('clients', function (Blueprint $table) {
        $table->dropColumn('lat');
        $table->dropColumn('lon');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('resellers', function (Blueprint $table) {
        $table->dropColumn('email');
      });
      Schema::table('clients', function (Blueprint $table) {
        $table->string('lat', 255)->nullable();
        $table->string('lon', 255)->nullable();
      });
    }
};
