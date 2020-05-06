<?php

namespace App\Services\Video;

interface ProviderInterface
{
  /**
   * Returns true if applicable for the given URL.
   */
  function check(string $url): bool;

  /**
   * Returns short video info, needed for preview, as an array.
   */
  function getPreviewData(string $url): array;

  /**
   * Returns video info as an array.
   */
  function getData(string $url): array;
}
