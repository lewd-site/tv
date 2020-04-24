<?php

namespace App\Services;

use App\Events\ChatMessageCreatedEvent;
use App\Events\VideoCreatedEvent;
use App\Models\ChatMessage;
use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use DateInterval;
use Illuminate\Support\Carbon;
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

  /**
   * @throws BadRequestHttpException
   */
  public function addVideo(Room $room, User $user, string $url): Video
  {
    $matches = [];
    if (!preg_match('/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([A-Za-z0-9_-]+).*$/', $url, $matches)) {
      throw new BadRequestHttpException("$url is not a valid YouTube URL");
    }

    $videoId = $matches[1];
    $key = config('services.youtube.key');
    $serviceUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$videoId&key=$key";

    $response = file_get_contents($serviceUrl);
    if ($response === false) {
      throw new BadRequestHttpException("Can't get video info");
    }

    $data = json_decode($response, true);
    if (empty($data['items'])) {
      throw new BadRequestHttpException("Video $url not found");
    }

    $url = "https://www.youtube.com/watch?v=$videoId";

    $item = $data['items'][0];
    $title = $item['snippet']['title'];

    $durationInterval = new DateInterval($item['contentDetails']['duration']);
    $duration = $durationInterval->s + 60 * $durationInterval->i + 3600 * $durationInterval->h + 86400 * $durationInterval->d;

    /** @var ?string */
    $playlistEndAt = Video::where('room_id', $room->id)
      ->where('end_at', '>', now())
      ->selectRaw('max(end_at) as end_at')
      ->pluck('end_at')
      ->first();

    $startAt = new Carbon($playlistEndAt);
    $endAt = $startAt->clone()->addSeconds($duration);

    $video = Video::create([
      'url'      => $url,
      'type'     => 'youtube',
      'title'    => $title,
      'start_at' => $startAt,
      'end_at'   => $endAt,
      'room_id'  => $room->id,
      'user_id'  => $user->id,
    ]);

    event(new VideoCreatedEvent($video));

    return $video;
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
