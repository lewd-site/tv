<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RoomController extends Controller
{
  protected RoomService $roomService;

  public function __construct(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function list()
  {
    $rooms = Room::all();

    return view('rooms.list', ['rooms' => $rooms]);
  }

  public function show($url)
  {
    $room = Room::where(['url' => $url])->first();
    if (!isset($room)) {
      abort(404);
    }

    return view('rooms.show', [
      'room'  => $room,
      'owner' => $room->owner,
    ]);
  }

  public function create()
  {
    return view('rooms.create');
  }

  public function createSubmit(Request $request)
  {
    $input = $request->validate([
      'url'  => 'required',
      'name' => 'required',
    ]);

    $userId = auth()->id();

    try {
      $room = $this->roomService->create($input['url'], $input['name'], $userId);
    } catch (BadRequestHttpException $e) {
      return redirect()->back()->withErrors(['url' => $e->getMessage()]);
    } catch (ConflictHttpException $e) {
      return redirect()->back()->withErrors(['url' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', $room->url);
  }
}
