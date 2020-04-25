<?php

namespace App\Services;

use App\Events\ChatMessageCreatedEvent;
use App\Models\ChatMessage;
use App\Models\Room;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RoomService
{
  /**
   * @throws BadRequestHttpException
   * @throws ConflictHttpException
   */
  public function create(User $user, string $url, string $name): Room
  {
    /** @var ?Room */
    $room = Room::where('url', $url)->first();
    if (isset($room)) {
      throw new ConflictHttpException("Room /$url already exists");
    }

    static $reserved = [];
    if (empty($reserved)) {
      $reserved = [
        'about',
        'admin',
        'api',
        'broadcasting',
        'contact',
        'create',
        'delete',
        'donate',
        'edit',
        'login',
        'logout',
        'register',
        'rooms',
        'update',
        'users',
      ];
    }

    if (in_array($url, $reserved)) {
      throw new BadRequestHttpException("URL /$url is reserved");
    }

    if (!preg_match('/[A-Za-z0-9_-]+/', $url)) {
      throw new BadRequestHttpException("URL should only contain alphanumeric and hyphens");
    }

    if (empty($name)) {
      throw new BadRequestHttpException('Name required');
    }

    return Room::create([
      'url'     => $url,
      'name'    => $name,
      'user_id' => $user->id,
    ]);
  }

  public function delete(Room $room): void
  {
    $room->delete();
  }

  public function addChatMessage(Room $room, User $user, string $message): ChatMessage
  {
    $message = ChatMessage::create([
      'message' => $message,
      'user_id' => $user->id,
      'room_id' => $room->id,
    ]);

    event(new ChatMessageCreatedEvent($message));

    return $message;
  }
}
