<?php

namespace App\Console\Commands;

use App\Services\RoomService;
use Illuminate\Console\Command;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    try {
      $this->roomService->delete($url);
    } catch (NotFoundHttpException $e) {
      $this->error($e->getMessage());

      return 1;
    }
  }
}
