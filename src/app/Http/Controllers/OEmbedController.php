<?php

namespace App\Http\Controllers;

use App\Services\OEmbedService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OEmbedController extends Controller
{
  protected OEmbedService $oembedService;

  public function __construct(OEmbedService $oembedService)
  {
    $this->oembedService = $oembedService;
  }

  public function oembed(Request $request)
  {
    $format = $request->query('format', 'json');
    if ($format !== 'json') {
      return response()->json(['error' => 'Not Implemented'], 501);
    }

    $maxWidth = $request->query('maxwidth');
    if (isset($maxWidth)) {
      if (is_numeric($maxWidth)) {
        $maxWidth = (int) $maxWidth;
      } else {
        return response()->json(['error' => 'Bad Request'], 400);
      }
    }

    $maxHeight = $request->query('maxheight');
    if (isset($maxHeight)) {
      if (is_numeric($maxHeight)) {
        $maxHeight = (int) $maxHeight;
      } else {
        return response()->json(['error' => 'Bad Request'], 400);
      }
    }

    $url = $request->query('url');
    if (empty($url)) {
      return response()->json(['error' => 'Bad Request'], 400);
    }

    try {
      $data = $this->oembedService->oembed($url, $maxWidth, $maxHeight);
    } catch (NotFoundHttpException $e) {
      return response()->json(['error' => 'Not Found'], 404);
    }

    return response()->json($data);
  }
}
