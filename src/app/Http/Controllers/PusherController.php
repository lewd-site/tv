<?php

namespace App\Http\Controllers;

use App\Services\UserCountService;
use Illuminate\Http\Request;

class PusherController extends Controller
{
  private UserCountService $userCountService;

  public function __construct(UserCountService $userCountService)
  {
    $this->userCountService = $userCountService;
  }

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
        if ($event['name'] === 'member_added') {
          $this->userCountService->add($roomId, $userId);
        } elseif ($event['name'] === 'member_removed') {
          $this->userCountService->remove($roomId, $userId);
        }
      }
    }

    return response('', 204);
  }
}
