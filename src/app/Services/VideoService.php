<?php

namespace App\Services;

use App\Events\VideoDeletedEvent;
use App\Models\Video;

class VideoService
{
  public function delete(Video $video): void
  {
    event(new VideoDeletedEvent($video));

    $video->delete();
  }

  public function deleteEnded(): void
  {
    $videos = Video::where('end_at', '<', now())
      ->select('id', 'room_id')
      ->get();

    foreach ($videos as $video) {
      $this->delete($video);
    }
  }
}
