<?php

namespace Tests\Unit\Services;

use App\Services\OEmbed\YouTubeProvider;
use App\Services\OEmbedService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class OEmbedServiceTest extends TestCase
{
  public function test_oembed(): void
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

    $this->mock(YouTubeProvider::class, function ($mock) use ($fakeResponse) {
      $mock->shouldReceive('check')->andReturn(true)->once();
      $mock->shouldReceive('getData')->andReturn($fakeResponse)->once();
    });

    /** @var OEmbedService */
    $service = app()->make(OEmbedService::class);
    $result = $service->oembed('https://www.youtube.com/watch?v=dQw4w9WgXcQ', null, null);

    $this->assertEquals($fakeResponse, $result);
  }

  public function test_oembed_invalidVideoUrl(): void
  {
    $this->mock(YouTubeProvider::class, function ($mock) {
      $mock->shouldReceive('check')->andReturn(false)->once();
      $mock->shouldReceive('getData')->never();
    });

    $this->expectException(NotFoundHttpException::class);

    /** @var OEmbedService */
    $service = app()->make(OEmbedService::class);
    $service->oembed('https://www.google.com/', null, null);
  }
}
