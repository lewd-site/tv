<?php

namespace App\Services;

use App\Models\Room;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoomService
{
  /**
   * @throws BadRequestHttpException
   * @throws ConflictHttpException
   */
  public function create(string $url, string $name, int $userId): Room
  {
    static $reserved = [];
    if (empty($reserved)) {
      $reserved = [
        'about',
        'admin',
        'api',
        'contact',
        'create',
        'donate',
        'login',
        'logout',
        'register',
        'rooms',
        'users',
      ];
    }

    if (in_array($url, $reserved)) {
      throw new BadRequestHttpException("URL $url is reserved");
    }

    if (!preg_match('/[A-Za-z0-9_-]+/', $url)) {
      throw new BadRequestHttpException("URL $url should only contain alphanumeric and hyphens");
    }

    if (empty($name)) {
      throw new BadRequestHttpException('Name required');
    }

    if ($userId <= 0) {
      throw new BadRequestHttpException('Owner ID required');
    }

    /** @var ?Room */
    $room = Room::where('url', $url)->first();
    if (isset($room)) {
      throw new ConflictHttpException("Room $url already exists");
    }

    return Room::create([
      'url'     => $url,
      'name'    => $name,
      'user_id' => $userId,
    ]);
  }

  /**
   * @throws NotFoundHttpException
   */
  public function delete(string $url): void
  {
    /** @var ?Room */
    $room = Room::where('url', $url)->first();
    if (!isset($room)) {
      throw new NotFoundHttpException("Room $url not found");
    }

    $room->delete();
  }
}
