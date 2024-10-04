<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\UpsertUserAddressRequest;
use App\Http\Resources\UserAddressResource;
use App\Http\Rules\IsSameUserActionRule;
use App\Services\UserAddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserAddressController
 *
 * @package App\Http\Controllers\Api
 */
class UserAddressController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    private UserAddressService $userAddressService;

    public function __construct(UserAddressService $userAddressService)
    {
        $this->userAddressService = $userAddressService;
    }

    public function index(Request $request) {
        $limit = $request->get('limit', self::ITEM_PER_PAGE);

        $user = Auth::user();
        $userAddresses = $this->userAddressService->getUserAddressesQuery($user->id)->paginate($limit);
        return UserAddressResource::collection($userAddresses);
    }

    public function show(Request $request, $id) {
        $request->merge(['user_address_id' => $request->route('address')]);
        $request->validate(['user_address_id' => ['exists:user_addresses,id', new IsSameUserActionRule()]]);

        $user = Auth::user();
        $userAddress = $this->userAddressService->getUserAddressesQuery($user->id, $id)->first();

        return new UserAddressResource($userAddress);
    }

    public function store(UpsertUserAddressRequest $request) {
        $user = Auth::user();
        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $country = $request->get('country');
        $province = $request->get('province');
        $city = $request->get('city');
        $zip = $request->get('zip');
        $comment = $request->get('comment');

        $userAddress = $this->userAddressService->upsert($user->id, null, $name, $phone, $address, $country, $province, $city, $zip, $comment);
        return new UserAddressResource($userAddress);
    }

    public function update(UpsertUserAddressRequest $request, $id) {
        $request->merge(['user_address_id' => $request->route('address')]);
        $request->validate(['user_address_id' => 'exists:user_addresses,id']);

        $user = Auth::user();
        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $country = $request->get('country');
        $province = $request->get('province');
        $city = $request->get('city');
        $zip = $request->get('zip');
        $comment = $request->get('comment');

        $userAddress = $this->userAddressService->upsert($user->id, $id, $name, $phone, $address, $country, $province, $city, $zip, $comment);
        return new UserAddressResource($userAddress);
    }

    public function destroy(Request $request, int $id) {
        $request->merge(['user_address_id' => $request->route('address')]);
        $request->validate(['user_address_id' => 'exists:user_addresses,id']);

        $user = Auth::user();
        $userAddress = $this->userAddressService->getUserAddressesQuery($user->id, $id)->first();
        return $userAddress->delete();
    }
}
