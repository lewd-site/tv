<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\User;
use Illuminate\Console\Command;

class ListRooms extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'room:list';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'List rooms';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $rooms = Room::query()
      ->join('users', 'rooms.user_id', '=', 'users.id')
      ->select('rooms.url', 'rooms.name AS room_name', 'users.name AS user_name')
      ->orderBy('rooms.url')
      ->get()
      ->toArray();

    $this->table(['URL', 'Name', 'Owner'], $rooms);
  }
}
