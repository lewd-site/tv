<?php

namespace App\Services\OEmbed;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class YouTubeProvider implements ProviderInterface
{
  const URL_PATTERN = '/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?.*v=[0-9A-Za-z_-]{10}[048AEIMQUYcgkosw]/';

  public function check(string $url): bool
  {
    static $data = [];

    /** @var bool[] $data */
    if (isset($data[$url])) {
      return $data[$url];
    }

    return $data[$url] = (bool) preg_match(static::URL_PATTERN, $url);
  }

  /**
   * @throws NotFoundHttpException
   */
  public function getData(string $url, ?int $maxWidth, ?int $maxHeight): array
  {
    if (!$this->check($url)) {
      throw new NotFoundHttpException('Video not found');
    }

    $oembedUrl = 'https://www.youtube.com/oembed?url=' . urlencode($url);

    if (isset($maxWidth)) {
      $oembedUrl .= "&maxwidth=$maxWidth";
    }

    if (isset($maxHeight)) {
      $oembedUrl .= "&maxheight=$maxHeight";
    }

    $response = $response = Http::get($oembedUrl);
    if (!$response->ok()) {
      throw new NotFoundHttpException('Video not found');
    }

    return $response->json();
  }
}
