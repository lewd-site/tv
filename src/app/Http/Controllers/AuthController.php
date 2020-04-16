<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AuthController extends Controller
{
  protected UserService $userService;

  public function __construct(UserService $userService)
  {
    $this->userService = $userService;
  }

  /**
   * Returns login form.
   */
  public function login()
  {
    return view('auth.pages.login');
  }

  /**
   * Handles login form submit.
   */
  public function loginSubmit(Request $request)
  {
    $credentials = $request->validate([
      'email'    => 'required|email',
      'password' => 'required|min:8',
    ]);

    if (!Auth::attempt($credentials)) {
      return redirect()->back()->withErrors([
        'email' => 'User not found or password is incorrect',
      ]);
    }

    return redirect()->intended(route('rooms.list'));
  }

  /**
   * Returns register form.
   */
  public function register()
  {
    return view('auth.pages.register');
  }

  /**
   * Handles register form submit.
   */
  public function registerSubmit(Request $request)
  {
    $credentials = $request->validate([
      'name'     => 'required',
      'email'    => 'required|email',
      'password' => 'required|min:8',
    ]);

    try {
      $user = $this->userService->create(
        $credentials['name'],
        $credentials['email'],
        $credentials['password']
      );
    } catch (ConflictHttpException $e) {
      return redirect()->back()->withErrors(['name' => $e->getMessage()]);
    }

    Auth::login($user);

    return redirect()->route('rooms.list');
  }

  public function logout()
  {
    Auth::logout();

    return redirect()->route('rooms.list');
  }
}
