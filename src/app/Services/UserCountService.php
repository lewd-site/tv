<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class UserCountService
{
  private function getRoomKey(int $roomId): string
  {
    return "rooms:$roomId:users";
  }

  public function add(int $roomId, int $userId): void
  {
    $key = $this->getRoomKey($roomId);
    Redis::sadd($key, $userId);
  }

  public function remove(int $roomId, int $userId): void
  {
    $key = $this->getRoomKey($roomId);
    Redis::srem($key, $userId);
  }

  public function getCount(int $roomId): int
  {
    $key = $this->getRoomKey($roomId);
    return (int) Redis::scard($key);
  }
}
