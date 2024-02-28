<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('database_connections', function (Blueprint $table) {
          $table->string('distributorName')->nullable();
          $table->string('driver')->default('mysql');
          $table->string('port');
          $table->string('database');
          $table->string('username');
          $table->string('password');
          $table->string('charset')->default('utf8mb4');
          $table->string('collation')->default('utf8mb4_unicode_ci');
          $table->string('prefix')->default('');
          $table->string('unix_socket')->nullable();// only for cloud
          $table->string('host');
          $table->boolean('prefix_indexes')->default(true);
          $table->boolean('strict')->default(true);
          $table->string('engine')->nullable();
          $table->mediumText('options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('database_connections');
    }
};
