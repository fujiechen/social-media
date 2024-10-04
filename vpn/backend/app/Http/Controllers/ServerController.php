<?php

namespace App\Http\Controllers;

use App\Services\ServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    private ServerService $serverService;

    public function __construct(ServerService $serverService) {
        $this->serverService = $serverService;
    }

    public function isAnyServerConnected(Request $request): JsonResponse {
        $ip = $request->ip();
        $connected = false;
        if ($ip) {
            $server = $this->serverService->findServerByIp($ip);
            if ($server) {
                $connected = true;
            }
        }

        return response()->json([
            'data' => [
                'connected' => $connected,
            ]
        ]);
    }
}
