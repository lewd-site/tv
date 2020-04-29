<?php

namespace App\Services\Video;

interface ProviderInterface
{
  /**
   * Returns true if applicable for the given URL.
   */
  function check(string $url): bool;

  /**
   * Returns video info as an array.
   */
  function getData(string $url): array;
}
