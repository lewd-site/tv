<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Console\Command;

class DeleteRoom extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'room:delete';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Delete room';

  protected RoomService $roomService;

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct(RoomService $roomService)
  {
    parent::__construct();

    $this->roomService = $roomService;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $url = $this->ask('URL');
    $room = Room::where(['url' => $url])->first();
    if (!isset($room)) {
      $this->error("Room /$url not found");

      return 1;
    }

    $this->roomService->delete($room);
  }
}
