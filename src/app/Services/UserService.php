<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserService
{
  /**
   * @throws ConflictHttpException
   */
  public function create(string $name, string $email, string $password): User
  {
    /** @var ?User */
    $user = User::where('email', $email)->first();
    if (isset($user)) {
      throw new ConflictHttpException("User $email already exists");
    }

    return User::create([
      'email'    => $email,
      'name'     => $name,
      'password' => Hash::make($password),
    ]);
  }

  public function delete(User $user): void
  {
    $user->delete();
  }
}
