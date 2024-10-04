<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function index(): JsonResponse {

        $metas = Meta::query()
            ->pluck( 'meta_value', 'meta_key')
            ->toArray();

        $result = [];
        foreach(Meta::META_KEYS as $k) {
            if (isset($metas[$k])) {
                $result[] = [
                    'meta_key' => $k,
                    'meta_value' => $metas[$k],
                ];
            } else {
                $result[] = [
                    'meta_key' => $k,
                    'meta_value' => '',
                ];
            }
        }

        return response()->json([
            'data' => $result
        ]);
    }
}
