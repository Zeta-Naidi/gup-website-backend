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
        Schema::create('appsIcons', function (Blueprint $table) {
            $table->id();
            $table->string('identifier', 191)->index();
            $table->string('osType', 64);
            $table->text('iconBase64')->nullable();
            $table->unique(['identifier','osType']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appsIcons');
    }
};
