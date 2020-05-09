<?php

namespace App\Services\Video;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnilibriaProvider implements ProviderInterface
{
  /** @var string[] */
  const URL_PATTERNS = [
    // Single episode.
    '/^(?:https?:\/\/)?(?:www\.)?anilibria\.tv\/release\/([0-9a-z-]+)\.html#(\d+)/',
    '/^(?:https?:\/\/)?(?:www\.)?anilibria\.tv\/public\/iframe.php\?.*id=(\d+)#(\d+)/',

    // Release.
    '/^(?:https?:\/\/)?(?:www\.)?anilibria\.tv\/release\/([0-9a-z-]+)\.html/',
    '/^(?:https?:\/\/)?(?:www\.)?anilibria\.tv\/public\/iframe.php\?.*id=(\d+)/',
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
          'index' => $matches[2] ?? -1,
        ];

        return true;
      }
    }

    static::$data[$url] = [
      'valid' => false,
      'id'    => null,
      'index' => null,
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

    $serviceUrl = 'https://www.anilibria.tv/public/api/index.php';
    $data = ['query' => 'release'];

    $id = static::$data[$url]['id'];
    if (is_numeric($id)) {
      $data['id'] = $id;
    } else {
      $data['code'] = $id;
    }

    $response = Http::asForm()->post($serviceUrl, $data);

    if (!$response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    $responseData = $response->json();
    if (empty($responseData['data'])) {
      throw new NotFoundHttpException('Video not found');
    }

    $data = $responseData['data'];
    $index = static::$data[$url]['index'];
    if ($index === -1) {
      $episodes = array_reverse(array_map(function ($item) {
        return ['title' => $item['title']];
      }, $data['playlist']));

      return [
        'title'        => $data['names'][0],
        'thumbnailUrl' => 'https://www.anilibria.tv' . $data['poster'],
        'episodes'     => $episodes,
      ];
    } else {
      $playlistIndex = count($data['playlist']) - $index;
      if (!isset($data['playlist'][$playlistIndex])) {
        throw new NotFoundHttpException('Video not found');
      }

      $episode = $data['playlist'][$playlistIndex];

      return [
        'title'        => $data['names'][0] . ' – ' . $episode['title'],
        'thumbnailUrl' => 'https://www.anilibria.tv' . $data['poster'],
      ];
    }
  }

  private function getM3u8Duration(string $data): float
  {
    $duration = 0.0;
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
      if (preg_match('/^#EXTINF:([0-9.]+),/', $line, $matches)) {
        $duration += (float) $matches[1];
      }
    }

    return $duration;
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
    $index = static::$data[$url]['index'];

    $serviceUrl = 'https://www.anilibria.tv/public/api/index.php';
    $data = ['query' => 'release'];

    $id = static::$data[$url]['id'];
    if (is_numeric($id)) {
      $data['id'] = $id;
    } else {
      $data['code'] = $id;
    }

    $response = Http::asForm()->post($serviceUrl, $data);
    if (!$response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    $responseData = $response->json();
    if (empty($responseData['data'])) {
      throw new NotFoundHttpException('Video not found');
    }

    if (!is_numeric($id)) {
      $id = $responseData['data']['id'];
    }

    $data = $responseData['data'];
    $playlistIndex = count($data['playlist']) - $index;
    if (!isset($data['playlist'][$playlistIndex])) {
      throw new NotFoundHttpException('Video not found');
    }

    $episode = $data['playlist'][$playlistIndex];
    $m3u8Response = Http::get($episode['hd']);
    if (!$m3u8Response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    $data = [
      'url'      => "https://www.anilibria.tv/public/iframe.php?id=$id#$index",
      'type'     => 'hls',
      'title'    => $data['names'][0] . ' – ' . $episode['title'],
      'duration' => $this->getM3u8Duration($m3u8Response->body()),
      'sources'  => [
        [
          'url'   => strtok($episode['sd'], '?'),
          'title' => 'sd',
        ],
        [
          'url'     => strtok($episode['hd'], '?'),
          'title'   => 'hd',
          'default' => true,
        ],
      ],
    ];

    if (!empty($episode['fullhd'])) {
      $data['sources'][] = [
        'url'     => strtok($episode['fullhd'], '?'),
        'title'   => 'fullhd',
      ];
    }

    return $data;
  }
}
