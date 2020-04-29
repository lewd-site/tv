<?php

namespace Tests\Unit\Services;

use App\Models\Room;
use App\Models\User;
use App\Services\RoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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
    $service->create($user, $url, $name);

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
    $service->create($user, $url, 'Room');
  }

  public function test_delete(): void
  {
    $url = 'room';
    $room = factory(Room::class)->create(['url' => $url]);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->delete($room);

    $this->assertDatabaseMissing('rooms', ['url' => $url]);
  }

  public function test_addChatMessage(): void
  {
    Event::fake();

    $url = 'room';
    /** @var Room */
    $room = factory(Room::class)->create(['url' => $url]);

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->addChatMessage($room, $user, $message);

    $this->assertDatabaseHas('chat_messages', [
      'message' => $message,
      'user_id' => $user->id,
      'room_id' => $room->id,
    ]);
  }
}
