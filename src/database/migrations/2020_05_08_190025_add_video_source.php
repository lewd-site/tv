<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVideoSource extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('video_sources', function (Blueprint $table) {
      $table->id();
      $table->string('url', 2048);
      $table->string('title');
      $table->boolean('default')->default('false');
      $table->bigInteger('video_id')->index();
      $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
      $table->unique(['video_id', 'url']);
    });

    $this->upgradeData();
  }

  private function upgradeData()
  {
    $videos = DB::table('videos')->where('type', 'html5')->get(['id', 'url']);

    $videoSources = $videos->map(fn ($video) => [
      'url'      => $video->url,
      'title'    => 'Default',
      'default'  => true,
      'video_id' => $video->id,
    ])->toArray();

    DB::table('video_sources')->insert($videoSources);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('video_sources');
  }
}
