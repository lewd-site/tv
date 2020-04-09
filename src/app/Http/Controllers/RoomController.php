<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoomController extends Controller
{
  public function list(Request $request)
  {
    return view('rooms.list');
  }

  public function show(Request $request)
  {
    return view('rooms.show');
  }

  public function create(Request $request)
  {
    return view('rooms.create');
  }
}
