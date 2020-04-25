<?php

namespace App\Services;

use App\Events\VideoCreatedEvent;
use App\Events\VideoDeletedEvent;
use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use DateInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VideoService
{
  const CACHE_TTL = 4 * 60 * 60;

  /**
   * @throws BadRequestHttpException
   */
  protected function getYouTubeInfo(string $url): array
  {
    $matches = [];
    if (!preg_match('/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([A-Za-z0-9_-]+).*$/', $url, $matches)) {
      throw new BadRequestHttpException("$url is not a valid YouTube URL");
    }

    $id = $matches[1];
    $cacheKey = "video:youtube:$id";
    if (Cache::has($cacheKey)) {
      $data = Cache::get($cacheKey);
    } else {
      $key = config('services.youtube.key');
      $serviceUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$id&key=$key";
      $response = file_get_contents($serviceUrl);
      if ($response === false) {
        throw new BadRequestHttpException("Can't get video info");
      }

      $responseData = json_decode($response, true);
      if (empty($responseData['items'])) {
        throw new BadRequestHttpException("Video $url not found");
      }

      $item = $responseData['items'][0];
      $interval = new DateInterval($item['contentDetails']['duration']);
      $duration = $interval->s + 60 * $interval->i + 3600 * $interval->h + 86400 * $interval->d;

      $data = [
        'url'      => "https://www.youtube.com/watch?v=$id",
        'type'     => 'youtube',
        'title'    => $item['snippet']['title'],
        'duration' => $duration,
      ];

      Cache::put($cacheKey, $data, static::CACHE_TTL);
    }

    return $data;
  }

  /**
   * @throws BadRequestHttpException
   */
  public function create(Room $room, User $user, string $url): Video
  {
    $data = $this->getYouTubeInfo($url);

    /** @var ?string */
    $playlistEndAt = Video::where('room_id', $room->id)
      ->where('end_at', '>', now())
      ->selectRaw('max(end_at) as end_at')
      ->pluck('end_at')
      ->first();

    $startAt = new Carbon($playlistEndAt);
    $endAt = $startAt->clone()->addSeconds($data['duration']);

    $video = Video::create([
      'url'      => $data['url'],
      'type'     => $data['type'],
      'title'    => $data['title'],
      'start_at' => $startAt,
      'end_at'   => $endAt,
      'room_id'  => $room->id,
      'user_id'  => $user->id,
    ]);

    App::terminating(fn () => event(new VideoCreatedEvent($video)));

    return $video;
  }

  public function delete(Video $video): void
  {
    App::terminating(fn () => event(new VideoDeletedEvent($video)));

    $video->delete();
  }

  public function deleteEnded(): void
  {
    $videos = Video::where('end_at', '<', now())
      ->select('id', 'room_id')
      ->get();

    foreach ($videos as $video) {
      $this->delete($video);
    }
  }
}
