<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

class OEmbedControllerTest extends TestCase
{
  public function test_oembed(): void
  {
    $response = $this->get(route('common.oembed') . '?url=https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    $response->assertSuccessful();
    $response->assertJsonStructure([
      'type',
      'version',
      'title',
      'author_name',
      'author_url',
      'provider_name',
      'provider_url',
      'thumbnail_url',
      'thumbnail_width',
      'thumbnail_height',
      'width',
      'height',
      'html',
    ]);
  }

  public function test_oembed_invalidVideoUrl(): void
  {
    $response = $this->get(route('common.oembed') . '?url=https://www.google.com/');

    $response->assertNotFound();
  }
}
