<?php

namespace App\Services\Video;

use DateInterval;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YouTubeProvider implements ProviderInterface
{
  const URL_PATTERN = '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/';

  private static $data = [];

  public function check(string $url): bool
  {
    if (isset(static::$data[$url])) {
      return static::$data[$url]['valid'];
    }

    $valid = (bool) preg_match(static::URL_PATTERN, $url, $matches);

    static::$data[$url] = [
      'valid' => $valid,
      'id'    => $valid ? $matches[1] : null,
    ];

    return $valid;
  }

  /**
   * @throws NotFoundHttpException
   */
  public function getData(string $url): array
  {
    if (!$this->check($url)) {
      throw new NotFoundHttpException('Video not found');
    }

    $id = static::$data[$url]['id'];
    $key = config('services.youtube.key');
    $serviceUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$id&key=$key";
    $response = Http::get($serviceUrl);
    if (!$response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    $responseData = $response->json();
    if (empty($responseData['items'])) {
      throw new NotFoundHttpException('Video not found');
    }

    $item = head($responseData['items']);
    $interval = new DateInterval($item['contentDetails']['duration']);

    $data = [
      'url'      => "https://www.youtube.com/watch?v=$id",
      'type'     => 'youtube',
      'title'    => $item['snippet']['title'],
      'duration' => $interval->s + 60 * $interval->i + 3600 * $interval->h + 86400 * $interval->d,
    ];

    return $data;
  }
}
