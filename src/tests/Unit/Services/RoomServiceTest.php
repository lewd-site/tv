<?php

namespace Tests\Unit\Services;

use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class RoomServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_create(): void
  {
    /** @var User */
    $user = factory(User::class)->create();

    $url = 'room';
    $name = 'Room';

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->create($url, $name, $user->id);

    $this->assertDatabaseHas('rooms', [
      'url'     => $url,
      'name'    => $name,
      'user_id' => $user->id,
    ]);
  }

  public function test_create_conflict(): void
  {
    /** @var User */
    $user = factory(User::class)->create();

    $url = 'room';
    factory(Room::class)->create(['url' => $url]);

    $this->expectException(ConflictHttpException::class);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->create($url, 'Room', $user->id);
  }

  public function test_delete(): void
  {
    $url = 'room';
    factory(Room::class)->create(['url' => $url]);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->delete($url);

    $this->assertDatabaseMissing('rooms', ['url' => $url]);
  }

  public function test_delete_notFound(): void
  {
    $this->expectException(NotFoundHttpException::class);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->delete('room');
  }
}
