<?php

namespace App\Http\Controllers;

use App\Services\VideoService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VideoController extends Controller
{
  protected VideoService $videoService;

  public function __construct(VideoService $videoService)
  {
    $this->videoService = $videoService;
  }

  public function preview(Request $request)
  {
    $url = $request->query('url');
    if (empty($url)) {
      return response()->json(['error' => 'Bad Request'], 400);
    }

    try {
      $data = $this->videoService->getPreviewData($url);
    } catch (NotFoundHttpException $e) {
      return response()->json(['error' => 'Not Found'], 404);
    }

    return response()->json($data);
  }
}
