<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_create(): void
  {
    $name = 'User';
    $email = 'user@example.com';

    /** @var UserService */
    $service = app()->make(UserService::class);
    $service->create($name, $email, 'password');

    $this->assertDatabaseHas('users', [
      'name'  => $name,
      'email' => $email,
    ]);
  }

  public function test_create_conflict(): void
  {
    $email = 'user@example.com';
    factory(User::class)->create(['email' => $email]);

    $this->expectException(ConflictHttpException::class);

    /** @var UserService */
    $service = app()->make(UserService::class);
    $service->create('User', $email, 'password');
  }

  public function test_delete(): void
  {
    $email = 'user@example.com';
    factory(User::class)->create(['email' => $email]);

    /** @var UserService */
    $service = app()->make(UserService::class);
    $service->delete($email);

    $this->assertDatabaseMissing('users', ['email' => $email]);
  }

  public function test_delete_notFound(): void
  {
    $this->expectException(NotFoundHttpException::class);

    /** @var UserService */
    $service = app()->make(UserService::class);
    $service->delete('user@example.com');
  }
}
