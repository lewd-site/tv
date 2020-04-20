<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('chat_messages', function (Blueprint $table) {
      $table->id();
      $table->text('message');
      $table->bigInteger('user_id');
      $table->bigInteger('room_id')->index();
      $table->timestamp('created_at')->nullable();
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
    Schema::dropIfExists('chat_messages');
  }
}
