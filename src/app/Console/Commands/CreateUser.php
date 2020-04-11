<?php

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CreateUser extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'user:create';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creates user account';

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
    $name = $this->ask('Name');
    $email = $this->ask('E-Mail');
    $password = $this->secret('Password');

    try {
      $this->userService->create($name, $email, $password);
    } catch (ConflictHttpException $e) {
      $this->error($e->getMessage());

      return 1;
    }
  }
}
