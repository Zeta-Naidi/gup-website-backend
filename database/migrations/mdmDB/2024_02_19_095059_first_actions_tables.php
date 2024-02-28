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
      Schema::create('actions', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->unsignedBigInteger('deviceId');
        $table->json('params');
        $table->string('status');  // ricevuta/applicata/andata in errore (nel vecchio chimpa)
        $table->string('errorDetail')->nullable();
        $table->timestamp('sentAt')->nullable();
        $table->timestamp('executedAt');
        $table->timestamp('createdAt');
        $table->timestamp('updatedAt');
      });

      Schema::create('appleCheckins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('actionId');
        $table->json('params');
        $table->timestamp('sentAt')->nullable();
        $table->timestamp('executedAt');
        $table->timestamp('createdAt');
        $table->timestamp('updatedAt');
      });


      Schema::create('windowsCheckins', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('actionId');
        $table->json('params');
        $table->timestamp('sentAt')->nullable();
        $table->timestamp('executedAt');
        $table->timestamp('createdAt');
        $table->timestamp('updatedAt');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('actions');

      Schema::dropIfExists('appleCheckins');
      Schema::dropIfExists('windowsCheckins');
    }
};
