<?php

namespace App\Services;

use App\Events\VideoCreatedEvent;
use App\Events\VideoDeletedEvent;
use App\Models\Room;
use App\Models\User;
use App\Models\Video;
use App\Services\Video\ProviderInterface;
use App\Services\Video\YouTubeProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VideoService
{
  const CACHE_TTL = 4 * 60 * 60;

  /** @var ProviderInterface[] */
  private array $providers = [];

  public function __construct(YouTubeProvider $youTubeProvider)
  {
    $this->providers[] = $youTubeProvider;
  }

  /**
   * @throws NotFoundHttpException
   */
  private function getVideoInfo(string $url): array
  {
    $cacheKey = "video:$url";
    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    foreach ($this->providers as $provider) {
      if ($provider->check($url)) {
        $data = $provider->getData($url);
        Cache::put($cacheKey, $data, static::CACHE_TTL);

        return $data;
      }
    }

    throw new NotFoundHttpException('Video not found');
  }

  /**
   * @throws BadRequestHttpException
   * @throws NotFoundHttpException
   */
  public function create(Room $room, User $user, string $url, ?int $start, ?int $end): Video
  {
    $data = $this->getVideoInfo($url);

    if (isset($end)) {
      $data['duration'] = $end;
    }

    if (isset($start)) {
      $data['duration'] -= $start;
    }

    if ($data['duration'] < 0) {
      throw new BadRequestHttpException("Video duration can't be negative");
    }

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
      'offset'   => $start ?? 0,
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
