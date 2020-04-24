<?php

namespace Tests\Unit\Services;

use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_delete(): void
  {
    $url = 'https://www.youtube.com/watch?v=Pq_mbTSR-a0';
    $video = factory(Video::class)->create(['url' => $url]);

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $service->delete($video);

    $this->assertDatabaseMissing('videos', ['url' => $url]);
  }

  public function test_deleteEnded(): void
  {
    $endedVideo = factory(Video::class)->create([
      'start_at' => now()->subMinute(10),
      'end_at' => now()->subMinute(5),
    ]);

    $notEndedVideo = factory(Video::class)->create([
      'start_at' => now()->addMinute(5),
      'end_at' => now()->addMinute(10),
    ]);

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $service->deleteEnded();

    $this->assertDatabaseMissing('videos', ['id' => $endedVideo->id]);
    $this->assertDatabaseHas('videos', ['id' => $notEndedVideo->id]);
  }
}
