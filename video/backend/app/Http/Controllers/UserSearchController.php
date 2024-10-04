<?php

namespace App\Http\Controllers;

use App\Services\UserSearchService;
use App\Transformers\UserSearchTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class UserSearchController extends Controller
{
    private UserSearchService $userSearchService;
    private Fractal $fractal;
    private UserSearchTransformer $userSearchTransformer;

    public function __construct(Fractal $fractal, UserSearchService $userSearchService, UserSearchTransformer $userSearchTransformer) {
        $this->userSearchService = $userSearchService;
        $this->fractal = $fractal;
        $this->userSearchTransformer = $userSearchTransformer;
    }

    public function history(Request $request): JsonResponse {
        $user = Auth::user();
        $userShares = $this->userSearchService->getUserSearchesHistoryQuery($user->id)->paginate($request->input('per_page', 10));
        $resource = new Collection($userShares->getCollection(), $this->userSearchTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($userShares));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function hot(Request $request):JsonResponse {
        $userShares = $this->userSearchService->getHotUserSearchesQuery()->paginate($request->input('per_page', 10));
        $resource = new Collection($userShares->getCollection(), $this->userSearchTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($userShares));
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
