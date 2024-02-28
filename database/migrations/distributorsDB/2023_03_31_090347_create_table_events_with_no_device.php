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
      Schema::create('events_not_stored_successfully', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('chimpaEventId');
        $table->unsignedBigInteger('clientId');
        $table->string('deviceSerialNumber', 191);
        $table->unsignedSmallInteger('type');
        $table->float('score');
        $table->string('criticalityLevel',32);
        $table->timestamp('detectionDate');
        $table->timestamp('updatedAt')->nullable();
        $table->string('description', 2048);
        $table->longText('docs');
        $table->unsignedSmallInteger('remediationType')->nullable();
        $table->string('remediationAction', 191)->nullable();
        $table->boolean('remediationActionStarted')->default(0);
        $table->boolean('hasBeenSolved')->default(0);
        $table->string('subject', 255)->nullable();
        $table->index('clientId');
        $table->index('type');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_not_stored_successfully');
    }
};
