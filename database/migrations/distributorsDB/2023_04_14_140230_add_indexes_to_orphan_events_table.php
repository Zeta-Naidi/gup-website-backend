<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('events_not_stored_successfully', function (Blueprint $table) {
      $table->unique(['chimpaEventId', 'clientId']);
      $table->index('chimpaEventId');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('events_not_stored_successfully', function (Blueprint $table) {
      $table->dropIndex(['chimpaEventId']);
      $table->dropUnique(['chimpaEventId', 'clientId']);
    });
  }
};
