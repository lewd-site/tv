<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
  public function show($id)
  {
    $user = User::with('rooms')->where(['id' => $id])->first();
    if (!isset($user)) {
      abort(404);
    }

    return view('users.pages.show', ['user' => $user]);
  }
}
