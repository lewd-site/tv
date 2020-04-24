<?php

namespace Tests\Feature\Console;

use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoTest extends TestCase
{
  use RefreshDatabase;

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

    $this->artisan('video:delete-ended')
      ->assertExitCode(0);

    $this->assertDatabaseMissing('videos', ['id' => $endedVideo->id]);
    $this->assertDatabaseHas('videos', ['id' => $notEndedVideo->id]);
  }
}
