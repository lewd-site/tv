<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Room;
use App\Models\Video;
use App\Services\RoomService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RoomController extends Controller
{
  const CHAT_MESSAGES = 100;

  protected RoomService $roomService;
  protected VideoService $videoService;

  public function __construct(RoomService $roomService, VideoService $videoService)
  {
    $this->roomService = $roomService;
    $this->videoService = $videoService;
  }

  public function list()
  {
    $rooms = Room::all();

    return view('rooms.pages.list', ['rooms' => $rooms]);
  }

  public function show(Room $room)
  {
    $messages = ChatMessage::with('user')
      ->where('room_id', $room->id)
      ->orderBy('created_at', 'desc')
      ->limit(static::CHAT_MESSAGES)
      ->get()
      ->reverse()
      ->values();

    $videos = Video::where('room_id', $room->id)
      ->orderBy('start_at', 'asc')
      ->get();

    return view('rooms.pages.show', [
      'room'     => $room,
      'videos'   => $videos,
      'messages' => $messages,
    ]);
  }

  public function create()
  {
    return view('rooms.pages.create');
  }

  public function createSubmit(Request $request)
  {
    $user = auth()->user();

    $input = $request->validate([
      'url'  => 'required',
      'name' => 'required',
    ]);

    try {
      $room = $this->roomService->create($user, $input['url'], $input['name']);
    } catch (BadRequestHttpException $e) {
      return redirect()->back()->withErrors(['url' => $e->getMessage()]);
    } catch (ConflictHttpException $e) {
      return redirect()->back()->withErrors(['url' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function videoSubmit(Request $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validate(['url' => 'required']);

    try {
      $this->videoService->create($room, $user, $input['url']);
    } catch (BadRequestHttpException $e) {
      return redirect()->back()->withErrors(['url' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function videoSubmitJson(Request $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validate(['url' => 'required']);

    try {
      $video = $this->videoService->create($room, $user, $input['url']);
    } catch (BadRequestHttpException $e) {
      return response()->json(['error' => $e->getMessage()], 400);
    }

    return response()->json($video->getViewModel(), 201, [
      'Location' => route('rooms.show', ['room' => $room->url]),
    ]);
  }

  public function chatSubmit(Request $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validate(['message' => 'required']);

    $this->roomService->addChatMessage($room, $user, $input['message']);

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function chatSubmitJson(Request $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validate(['message' => 'required']);

    $message = $this->roomService->addChatMessage($room, $user, $input['message']);

    return response()->json($message->getViewModel(), 201, [
      'Location' => route('rooms.show', ['room' => $room->url]),
    ]);
  }
}
