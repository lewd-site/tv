<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;

class DeleteUser extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'user:delete';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Delete user account';

  protected UserService $userService;

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct(UserService $userService)
  {
    parent::__construct();

    $this->userService = $userService;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $email = $this->ask('E-Mail');
    $user = User::where(['email' => $email])->first();
    if (!isset($user)) {
      $this->error("User $email not found");

      return 1;
    }

    $this->userService->delete($user);
  }
}
