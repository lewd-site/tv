<?php

namespace App\Services\Video;

use DateInterval;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YouTubeProvider implements ProviderInterface
{
  /** @var string[] */
  const URL_PATTERNS = [
    '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/',
    '/^(?:https?:\/\/)?(?:www\.)?youtu\.be\/([0-9A-Za-z_-]{10}[048AEIMQUYcgkosw])/',
  ];

  private static $data = [];

  public function check(string $url): bool
  {
    if (isset(static::$data[$url])) {
      return static::$data[$url]['valid'];
    }

    foreach (static::URL_PATTERNS as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        static::$data[$url] = [
          'valid' => true,
          'id'    => $matches[1],
        ];

        return true;
      }
    }

    static::$data[$url] = [
      'valid' => false,
      'id'    => null,
    ];

    return false;
  }

  /**
   * @throws NotFoundHttpException
   */
  public function getPreviewData(string $url): array
  {
    if (!$this->check($url)) {
      throw new NotFoundHttpException('Video not found');
    }

    $oembedUrl = 'https://www.youtube.com/oembed?url=' . urlencode($url);
    $response = $response = Http::get($oembedUrl);
    if (!$response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    $data = $response->json();

    return [
      'title'        => $data['title'],
      'thumbnailUrl' => $data['thumbnail_url'],
      'authorName'   => $data['author_name'],
      'authorUrl'    => $data['author_url'],
    ];
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

    return [
      'url'      => "https://www.youtube.com/watch?v=$id",
      'type'     => 'youtube',
      'title'    => $item['snippet']['title'],
      'duration' => $interval->s + 60 * $interval->i + 3600 * $interval->h + 86400 * $interval->d,
    ];
  }
}
