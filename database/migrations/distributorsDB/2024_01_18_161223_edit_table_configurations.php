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
      Schema::table('configurations', function (Blueprint $table) {
        $table->dropColumn('clientsFilterSetByUser');
        $table->json('resellerIds')->nullable();
        $table->json('clientIds')->nullable()->change();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('configurations', function (Blueprint $table) {
        $table->json('clientIds')->change();
        $table->dropColumn('resellerIds');
        $table->unsignedBigInteger('userId');
      });
    }
};
