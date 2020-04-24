<?php

namespace App\Events;

use App\Models\Video;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class VideoDeletedEvent implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets;

  public int $roomId;
  public int $videoId;

  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(Video $video)
  {
    $this->roomId = $video->room_id;
    $this->videoId = $video->id;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return \Illuminate\Broadcasting\Channel|array
   */
  public function broadcastOn()
  {
    return new Channel('rooms.' . $this->roomId);
  }

  /**
   * Get the data to broadcast.
   *
   * @return array
   */
  public function broadcastWith()
  {
    return ['id' => $this->videoId];
  }
}
