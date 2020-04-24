<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('videos', function (Blueprint $table) {
      $table->id();
      $table->string('url', 2048);
      $table->string('type');
      $table->string('title');
      $table->timestamp('start_at');
      $table->timestamp('end_at');
      $table->bigInteger('user_id');
      $table->bigInteger('room_id')->index();
      $table->foreign('user_id')->references('id')->on('users');
      $table->foreign('room_id')->references('id')->on('rooms');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('videos');
  }
}
