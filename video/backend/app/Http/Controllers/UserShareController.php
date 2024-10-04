<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserShareRequest;
use App\Models\UserShare;
use App\Services\UserService;
use App\Transformers\UserShareTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserShareController extends Controller
{
    private UserService $userService;
    private Fractal $fractal;
    private UserShareTransformer $userShareTransformer;

    public function __construct(Fractal $fractal, UserService $userService, UserShareTransformer $userShareTransformer) {
        $this->userService = $userService;
        $this->fractal = $fractal;
        $this->userShareTransformer = $userShareTransformer;
    }

    public function index(Request $request): JsonResponse {
        $userShares = $this->userService->findUserSharesQuery(Auth::user()->id)
            ->orderByDesc('id')->paginate($request->input('per_page'));
        $resource = new Collection($userShares->items(), $this->userShareTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($userShares));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function show(int $userShareId): JsonResponse {
        $userShare = UserShare::find($userShareId);
        $resource = new Item($userShare, $this->userShareTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function store(CreateUserShareRequest $request): JsonResponse {
        $dto = $request->toDto();
        $userShare = $this->userService->createUserShare($dto);
        $resource = new Item($userShare, $this->userShareTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function qrCode(int $userShareId) {
        $userShare = UserShare::find($userShareId);

        return QrCode::generate(
            $userShare->url . '?user_share_id=' . $userShare->id,
        );
    }


}
