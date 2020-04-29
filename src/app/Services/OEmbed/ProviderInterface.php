<?php

namespace App\Services\OEmbed;

interface ProviderInterface
{
  /**
   * Returns true if applicable for the given URL.
   */
  function check(string $url): bool;

  /**
   * Returns oEmbed data as an array.
   */
  function getData(string $url, ?int $maxWidth, ?int $maxHeight): array;
}
