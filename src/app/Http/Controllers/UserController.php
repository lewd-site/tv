<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Services\UserCountService;

class UserController extends Controller
{
  private UserCountService $userCountService;

  public function __construct(UserCountService $userCountService)
  {
    $this->userCountService = $userCountService;
  }

  public function show(User $user)
  {
    $rooms = $user->rooms;
    $rooms = $rooms->map(function (Room $room) {
      $room->userCount = $this->userCountService->getCount($room->id);

      return $room;
    });

    return view('users.pages.show', [
      'user'  => $user,
      'rooms' => $rooms,
    ]);
  }
}
