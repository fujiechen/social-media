<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLandingRequest;
use App\Services\LandingService;
use Illuminate\Http\JsonResponse;


class LandingController extends Controller
{
    private LandingService $landingService;

    public function __construct(LandingService $landingService) {
        $this->landingService = $landingService;
    }

    public function store(CreateLandingRequest $createLandingRequest): JsonResponse
    {
        $landing = $this->landingService->createLanding($createLandingRequest->toDto());
        return response()->json($landing->toArray());
    }
}
