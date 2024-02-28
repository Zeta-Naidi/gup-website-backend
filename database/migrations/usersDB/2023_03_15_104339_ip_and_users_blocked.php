<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('ip_users_blocked', function (Blueprint $table) {
        $table->id();
        $table->ipAddress('ip')->nullable()->index();
        $table->string('username',191)->nullable()->index();
        $table->integer('distributorId')->nullable()->index();
        $table->timestamp('blockedUntil')->index();
        $table->timestamp('blockedAt')->default(new Expression('(CURRENT_TIMESTAMP)'));
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('ip_users_blocked');
    }
};
