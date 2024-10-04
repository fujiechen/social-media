<?php

namespace App\Http\Controllers;

use App\Services\TutorialService;
use App\Transformers\TutorialTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Item;

class TutorialController extends Controller
{
    private Fractal $fractal;
    private TutorialService $tutorialService;
    private TutorialTransformer $tutorialTransformer;

    public function __construct(Fractal $fractal, TutorialService $tutorialService, TutorialTransformer $tutorialTransformer) {
        $this->fractal = $fractal;
        $this->tutorialService = $tutorialService;
        $this->tutorialTransformer = $tutorialTransformer;
    }

    public function show(string $os): JsonResponse {
        $tutorial = $this->tutorialService->fetchTutorialByOs($os);
        $resource = new Item($tutorial, $this->tutorialTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
