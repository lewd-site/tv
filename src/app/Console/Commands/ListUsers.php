<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'user:list';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'List user accounts';

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
    $users = User::query()
      ->select('email', 'name')
      ->orderBy('email')
      ->get()
      ->toArray();

    $this->table(['E-Mail', 'Name'], $users);
  }
}
