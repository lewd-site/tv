<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\CreateVideoRequest;
use App\Models\ChatMessage;
use App\Models\Room;
use App\Models\Video;
use App\Services\RoomService;
use App\Services\UserCountService;
use App\Services\VideoService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoomController extends Controller
{
  const CHAT_MESSAGES = 100;

  private RoomService $roomService;
  private VideoService $videoService;
  private UserCountService $userCountService;

  public function __construct(
    RoomService $roomService,
    VideoService $videoService,
    UserCountService $userCountService
  ) {
    $this->roomService = $roomService;
    $this->videoService = $videoService;
    $this->userCountService = $userCountService;
  }

  public function list()
  {
    $rooms = Room::all();
    $rooms = $rooms->map(function (Room $room) {
      $room->userCount = $this->userCountService->getCount($room->id);

      return $room;
    });

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
      'room'      => $room,
      'videos'    => $videos,
      'messages'  => $messages,
      'userCount' => $this->userCountService->getCount($room->id),
    ]);
  }

  public function create()
  {
    return view('rooms.pages.create');
  }

  public function createSubmit(CreateRoomRequest $request)
  {
    $user = auth()->user();
    $input = $request->validated();
    try {
      $room = $this->roomService->create($user, $input['url'], $input['name']);
    } catch (BadRequestHttpException | ConflictHttpException $e) {
      return redirect()->back()->withInput()->withErrors(['url' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function addVideo(Room $room)
  {
    return view('rooms.pages.add-video', ['room' => $room]);
  }

  private function timeToDuration(string $time): int
  {
    $parts = explode(':', $time);
    switch (count($parts)) {
      case 1:
        return (int) $parts[0];

      case 2:
        return 60 * (int) $parts[0] + (int) $parts[1];

      case 3:
        return 3600 * (int) $parts[0] + 60 * (int) $parts[1] + (int) $parts[2];

      default:
        throw new BadRequestHttpException('Invalid time format');
    }
  }

  public function addVideoSubmit(CreateVideoRequest $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validated();
    try {
      $start = isset($input['start']) ? $this->timeToDuration($input['start']) : null;
      $end = isset($input['end']) ? $this->timeToDuration($input['end']) : null;
      $this->videoService->create($room, $user, $input['url'], $start, $end);
    } catch (BadRequestHttpException | NotFoundHttpException $e) {
      return redirect()->back()->withInput()->withErrors(['url' => $e->getMessage()]);
    }

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function videoSubmitJson(CreateVideoRequest $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validated();
    try {
      $start = isset($input['start']) ? $this->timeToDuration($input['start']) : null;
      $end = isset($input['end']) ? $this->timeToDuration($input['end']) : null;
      $video = $this->videoService->create($room, $user, $input['url'], $start, $end);
    } catch (BadRequestHttpException | NotFoundHttpException $e) {
      return response()->json(['error' => $e->getMessage()], 400);
    }

    return response()->json($video->getViewModel(), 201, [
      'Location' => route('rooms.show', ['room' => $room->url]),
    ]);
  }

  public function chatSubmit(CreateMessageRequest $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validated();
    $this->roomService->addChatMessage($room, $user, $input['message']);

    return redirect()->route('rooms.show', ['room' => $room->url]);
  }

  public function chatSubmitJson(CreateMessageRequest $request, Room $room)
  {
    $user = auth()->user();
    $input = $request->validated();
    $message = $this->roomService->addChatMessage($room, $user, $input['message']);

    return response()->json($message->getViewModel(), 201, [
      'Location' => route('rooms.show', ['room' => $room->url]),
    ]);
  }
}
