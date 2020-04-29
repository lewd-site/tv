<?php

namespace Tests\Unit\Services\Video;

use App\Services\Video\YouTubeProvider;
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
    $id = 'dQw4w9WgXcQ';
    $url = "https://www.youtube.com/watch?v=$id";
    $title = 'Rick Astley - Never Gonna Give You Up (Video)';
    $minutes = 3;
    $seconds = 33;

    $fakeResponse = [
      'items' => [
        [
          'contentDetails' => ['duration' => "PT{$minutes}M{$seconds}S"],
          'snippet'        => ['title'    => $title],
        ],
      ],
    ];

    Http::fake([
      'googleapis.com/*' => Http::response($fakeResponse, 200),
      '*'                => Http::response([], 404),
    ]);

    /** @var YouTubeProvider */
    $provider = app()->make(YouTubeProvider::class);
    $result = $provider->getData($url, null, null);

    $this->assertEquals([
      'url'      => $url,
      'type'     => 'youtube',
      'title'    => $title,
      'duration' => $minutes * 60 + $seconds,
    ], $result);

    Http::assertSent(function (Request $request) use ($id) {
      $serviceUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$id";

      return strpos($request->url(), $serviceUrl) === 0;
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
