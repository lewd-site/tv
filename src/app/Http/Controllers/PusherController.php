<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PusherController extends Controller
{
  public function webhook(Request $request)
  {
    $key = $request->header('X-Pusher-Key');
    if (!isset($key)) {
      abort(401);
    }

    if ($key !== config('broadcasting.connections.pusher.key')) {
      abort(401);
    }

    $data = $request->json()->all();
    foreach ($data['events'] as $event) {
      if (preg_match('/^presence-rooms.(\d+)$/', $event['channel'], $matches)) {
        $roomId = $matches[1];
        $userId = $event['user_id'];

        $key = "rooms:$roomId:users";
        if ($event['name'] === 'member_added') {
          Redis::sadd($key, $userId);
        } elseif ($event['name'] === 'member_removed') {
          Redis::srem($key, $userId);
        }
      }
    }

    return response('', 204);
  }
}
