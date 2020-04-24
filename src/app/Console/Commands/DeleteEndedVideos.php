<?php

namespace App\Console\Commands;

use App\Services\VideoService;
use Illuminate\Console\Command;

class DeleteEndedVideos extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'video:delete-ended';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Delete ended videos';

  protected VideoService $videoService;

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct(VideoService $videoService)
  {
    parent::__construct();

    $this->videoService = $videoService;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $this->videoService->deleteEnded();
  }
}
