<?php

namespace Tests\Unit\Services;

use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use App\Services\Video\YouTubeProvider;
use App\Services\VideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class VideoServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_create(): void
  {
    /** @var Room */
    $room = factory(Room::class)->create();

    /** @var User */
    $user = factory(User::class)->create();

    $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
    $type = 'youtube';
    $title = 'Rick Astley - Never Gonna Give You Up (Video)';
    $fakeData = [
      'url'      => $url,
      'type'     => $type,
      'title'    => $title,
      'duration' => 3 * 60 + 33,
    ];

    $this->mock(YouTubeProvider::class, function ($mock) use ($fakeData) {
      $mock->shouldReceive('check')->andReturn(true)->once();
      $mock->shouldReceive('getData')->andReturn($fakeData)->once();
    });

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $video = $service->create($room, $user, $url);

    $this->assertEquals($url, $video->url);
    $this->assertEquals($type, $video->type);
    $this->assertEquals($title, $video->title);
    $this->assertEquals($room->id, $video->room_id);
    $this->assertEquals($user->id, $video->user_id);

    $this->assertDatabaseHas('videos', [
      'url'     => $url,
      'type'    => $type,
      'title'   => $title,
      'room_id' => $room->id,
      'user_id' => $user->id,
    ]);
  }

  public function test_create_invalidVideoUrl(): void
  {
    /** @var Room */
    $room = factory(Room::class)->create();

    /** @var User */
    $user = factory(User::class)->create();

    $url = 'https://www.google.com/';

    $this->mock(YouTubeProvider::class, function ($mock) {
      $mock->shouldReceive('check')->andReturn(false)->once();
      $mock->shouldReceive('getData')->never();
    });

    $this->expectException(NotFoundHttpException::class);

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $service->create($room, $user, $url);
  }

  public function test_delete(): void
  {
    Event::fake();

    $url = 'https://www.youtube.com/watch?v=Pq_mbTSR-a0';
    $video = factory(Video::class)->create(['url' => $url]);

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $service->delete($video);

    $this->assertDatabaseMissing('videos', ['url' => $url]);
  }

  public function test_deleteEnded(): void
  {
    Event::fake();

    $endedVideo = factory(Video::class)->create([
      'start_at' => now()->subMinute(10),
      'end_at'   => now()->subMinute(5),
    ]);

    $notEndedVideo = factory(Video::class)->create([
      'start_at' => now()->addMinute(5),
      'end_at'   => now()->addMinute(10),
    ]);

    /** @var VideoService */
    $service = app()->make(VideoService::class);
    $service->deleteEnded();

    $this->assertDatabaseMissing('videos', ['id' => $endedVideo->id]);
    $this->assertDatabaseHas('videos', ['id' => $notEndedVideo->id]);
  }
}
