<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\IndexUserProductRequest;
use App\Http\Resources\UserProductResource;
use App\Http\Resources\UserProductReturnResource;
use App\Services\UserProductReturnService;
use App\Services\UserProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @package App\Http\Controllers\Api
 */
class UserProductController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    private UserProductService $userProductService;
    private UserProductReturnService $userProductReturnService;

    public function __construct(
        UserProductService $userProductService,
        UserProductReturnService $userProductReturnService
    )
    {
        $this->userProductService = $userProductService;
        $this->userProductReturnService = $userProductReturnService;
    }

    public function index(IndexUserProductRequest $request)
    {
        $user = Auth::user();

        $isActive = $request->get('is_active', null);
        $limit = $request->get('limit', self::ITEM_PER_PAGE);

        $query = $this->userProductService->getUserProductsQuery($user->id, $isActive);
        return UserProductResource::collection($query->paginate($limit));
    }

    public function indexProductReturns(Request $request, int $userProductId)
    {
        $user = Auth::user();
        $orderByDirection = $request->get('order_by_direction', 'desc');

        $query = $this->userProductReturnService->getUserProductReturnsQuery($user->id, $userProductId, $orderByDirection);
        $limit = $request->get('limit', self::ITEM_PER_PAGE);
        return UserProductReturnResource::collection($query->paginate($limit));
    }
}
