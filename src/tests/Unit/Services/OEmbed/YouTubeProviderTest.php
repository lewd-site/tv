<?php

namespace Tests\Unit\Services\OEmbed;

use App\Services\OEmbed\YouTubeProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class YouTubeProviderTest extends TestCase
{
  public function test_check(): void
  {
    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $result = $provider->check('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    $this->assertTrue($result);
  }

  public function test_check_emptyUrl(): void
  {
    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $result = $provider->check('');

    $this->assertFalse($result);
  }

  public function test_check_invalidVideoUrl(): void
  {
    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $result = $provider->check('https://www.google.com/');

    $this->assertFalse($result);
  }

  public function test_getData(): void
  {
    $fakeResponse = [
      'type'             => 'video',
      'version'          => '1.0',
      'title'            => 'Rick Astley - Never Gonna Give You Up (Video)',
      'author_name'      => 'RickAstleyVEVO',
      'author_url'       => 'https://www.youtube.com/user/RickAstleyVEVO',
      'provider_name'    => 'YouTube',
      'provider_url'     => 'https://www.youtube.com/',
      'thumbnail_url'    => 'https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
      'thumbnail_width'  => 480,
      'thumbnail_height' => 360,
      'width'            => 480,
      'height'           => 270,
      'html'             => '<iframe></iframe>',
    ];

    Http::fake([
      'youtube.com/*' => Http::response($fakeResponse, 200),
      '*'             => Http::response([], 404),
    ]);

    $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $result = $provider->getData($url, null, null);

    $this->assertEquals($fakeResponse, $result);

    Http::assertSent(function (Request $request) use ($url) {
      return strpos($request->url(), 'https://www.youtube.com/oembed?url=' . urlencode($url)) === 0;
    });
  }

  public function test_getData_emptyUrl(): void
  {
    Http::fake();

    $this->expectException(NotFoundHttpException::class);

    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $provider->getData('', null, null);
  }

  public function test_getData_invalidVideoUrl(): void
  {
    Http::fake();

    $this->expectException(NotFoundHttpException::class);

    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $provider->getData('https://www.google.com/', null, null);
  }
}
