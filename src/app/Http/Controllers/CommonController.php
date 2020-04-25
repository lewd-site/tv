<?php

namespace App\Http\Controllers;

class CommonController extends Controller
{
  public function time()
  {
    return response()->json(['time' => now()->format('Y-m-d\TH:i:s.vP')]);
  }

  public function landing()
  {
    return view('common.pages.landing');
  }

  public function about()
  {
    return view('common.pages.about');
  }

  public function contact()
  {
    return view('common.pages.contact');
  }

  public function donate()
  {
    return view('common.pages.donate');
  }
}
