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
        $table->dropColumn('clientId');
        $table->dropColumn('resellerId');
        $table->dropColumn('distributorId');
        $table->json('clientIds');
      });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('configurations', function (Blueprint $table) {
        $table->integer('clientId')->nullable()->index();
        $table->integer('resellerId')->nullable()->index();
        $table->integer('distributorId')->nullable()->index();
        $table->dropColumn('clientIds');
      });
    }
};
