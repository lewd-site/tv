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

  public function test_addChatMessage(): void
  {
    $url = 'room';
    /** @var Room */
    $room = factory(Room::class)->create(['url' => $url]);

    $email = 'test@example.com';
    /** @var User */
    $user = factory(User::class)->create(['email' => $email]);

    $message = 'Test message';

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->addChatMessage($url, $email, $message);

    $this->assertDatabaseHas('chat_messages', [
      'message' => $message,
      'user_id' => $user->id,
      'room_id' => $room->id,
    ]);
  }

  public function test_addChatMessage_roomNotFound(): void
  {
    $email = 'test@example.com';
    factory(User::class)->create(['email' => $email]);

    $this->expectException(NotFoundHttpException::class);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->addChatMessage('room', $email, 'Test mesage');
  }

  public function test_addChatMessage_userNotFound(): void
  {
    $url = 'room';
    factory(Room::class)->create(['url' => $url]);

    $this->expectException(NotFoundHttpException::class);

    /** @var RoomService */
    $service = app()->make(RoomService::class);
    $service->addChatMessage($url, 'test@example.com', 'Test mesage');
  }
}
