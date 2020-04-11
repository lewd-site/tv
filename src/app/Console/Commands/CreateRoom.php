<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RoomService;
use Illuminate\Console\Command;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CreateRoom extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'room:create';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates room';

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
    $name = $this->ask('Name');
    $url = $this->ask('URL');

    $email = $this->ask("Owner's e-mail");
    $user = User::where(['email' => $email])->first();
    if (!isset($user)) {
      $this->error("User $email not found");

      return 1;
    }

    try {
      $this->roomService->create($url, $name, $user->id);
    } catch (BadRequestHttpException | ConflictHttpException $e) {
      $this->error($e->getMessage());

      return 1;
    }
  }
}
