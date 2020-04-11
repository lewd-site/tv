<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
  use RefreshDatabase;

  public function test_createUser(): void
  {
    $name = 'User';
    $email = 'user@example.com';
    $this->artisan('user:create')
      ->expectsQuestion('Name', $name)
      ->expectsQuestion('E-Mail', $email)
      ->expectsQuestion('Password', 'password')
      ->assertExitCode(0);

    $this->assertDatabaseHas('users', [
      'name'  => $name,
      'email' => $email,
    ]);
  }

  public function test_createUser_conflict(): void
  {
    $email = 'user@example.com';
    factory(User::class)->create(['email' => $email]);

    $this->artisan('user:create')
      ->expectsQuestion('Name', 'User')
      ->expectsQuestion('E-Mail', $email)
      ->expectsQuestion('Password', 'password')
      ->expectsOutput("User $email already exists")
      ->assertExitCode(1);
  }

  public function test_deleteUser(): void
  {
    $email = 'user@example.com';
    $user = factory(User::class)->create(['email' => $email]);

    $this->artisan('user:delete')
      ->expectsQuestion('E-Mail', $email)
      ->assertExitCode(0);

    $this->assertDeleted($user);
  }

  public function test_deleteUser_notFound(): void
  {
    $email = 'user@example.com';
    $this->artisan('user:delete')
      ->expectsQuestion('E-Mail', $email)
      ->expectsOutput("User $email not found")
      ->assertExitCode(1);
  }

  public function test_listUsers(): void
  {
    $this->artisan('user:list')
      ->assertExitCode(0);
  }
}
