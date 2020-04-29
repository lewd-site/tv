<?php

namespace App\Services;

use App\Services\OEmbed\ProviderInterface;
use App\Services\OEmbed\YouTubeProvider;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OEmbedService
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
  public function oembed(string $url, ?int $maxWidth, ?int $maxHeight): array
  {
    $cacheKey = "oembed:$url:$maxWidth:$maxHeight";
    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    foreach ($this->providers as $provider) {
      if ($provider->check($url)) {
        $data = $provider->getData($url, $maxWidth, $maxHeight);
        Cache::put($cacheKey, $data, static::CACHE_TTL);

        return $data;
      }
    }

    throw new NotFoundHttpException('Video not found');
  }
}
