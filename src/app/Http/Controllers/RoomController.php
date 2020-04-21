<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoomController extends Controller
{
  const CHAT_MESSAGES = 100;

  protected RoomService $roomService;

  public function __construct(RoomService $roomService)
  {
    $this->roomService = $roomService;
  }

  public function list()
  {
    $rooms = Room::all();

    return view('rooms.pages.list', ['rooms' => $rooms]);
  }

  public function show($url)
  {
    $room = Room::with('owner')->where('url', $url)->first();
    if (!isset($room)) {
      abort(404);
    }

    $messages = ChatMessage::with('user')
      ->where('room_id', $room->id)
      ->orderBy('created_at', 'desc')
      ->limit(static::CHAT_MESSAGES)
      ->get()
      ->reverse()
      ->values();

    return view('rooms.pages.show', [
      'room'     => $room,
      'messages' => $messages,
    ]);
  }

  public function create()
  {
    return view('rooms.pages.create');
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

  public function chatSubmit(Request $request, $url)
  {
    $input = $request->validate(['message' => 'required']);

    $user = auth()->user();
    $email = $user->email;

    try {
      $this->roomService->addChatMessage($url, $email, $input['message']);
    } catch (NotFoundHttpException $e) {
      return redirect()->back()->withErrors(['message' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', $url);
  }

  public function chatSubmitJson(Request $request, $url)
  {
    $input = $request->validate(['message' => 'required']);

    $user = auth()->user();
    $email = $user->email;

    try {
      $message = $this->roomService->addChatMessage($url, $email, $input['message']);
    } catch (NotFoundHttpException $e) {
      return response()->json(['error' => $e->getMessage()], 404);
    }

    return response()->json($message->getViewModel(), 201, [
      'Location' => route('rooms.show', ['url' => $url]),
    ]);
  }
}
