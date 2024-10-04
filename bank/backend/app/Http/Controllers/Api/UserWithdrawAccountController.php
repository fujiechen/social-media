<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpsertUserWithdrawAccountRequest;
use App\Http\Resources\UserWithdrawAccountResource;
use App\Http\Rules\IsSameUserActionRule;
use App\Services\UserWithdrawAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserWithdrawAccountController
 *
 * @package App\Http\Controllers\Api
 */
class UserWithdrawAccountController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    private UserWithdrawAccountService $userWithdrawAccountService;

    public function __construct(UserWithdrawAccountService $userWithdrawAccountService)
    {
        $this->userWithdrawAccountService = $userWithdrawAccountService;
    }

    public function index(Request $request)
    {
        $limit = $request->get('limit', self::ITEM_PER_PAGE);

        $user = Auth::user();
        $userWithdrawAccounts = $this->userWithdrawAccountService->getUserWithdrawAccountsQuery($user->id)->paginate($limit);
        return UserWithdrawAccountResource::collection($userWithdrawAccounts);
    }

    public function show(Request $request, $id)
    {
        $request->merge(['user_withdraw_account_id' => $request->route('account')]);
        $request->validate(['user_withdraw_account_id' => ['exists:user_withdraw_accounts,id', new IsSameUserActionRule()]]);

        $user = Auth::user();
        $userAddress = $this->userWithdrawAccountService->getUserWithdrawAccountsQuery($user->id, $id)->first();

        return new UserWithdrawAccountResource($userAddress);
    }

    public function store(UpsertUserWithdrawAccountRequest $request)
    {
        $user = Auth::user();

        $name = $request->get('name');
        $phone = $request->get('phone');
        $accountNumber = $request->get('account_number');
        $bankAddress = $request->get('bank_address');
        $bankName = $request->get('bank_name');
        $comment = $request->get('comment');

        $userWithdrawAccount = $this->userWithdrawAccountService->upsert(
            $user->id,
            null,
            $name, $phone,
            $accountNumber,
            $bankAddress,
            $bankName,
            $comment
        );
        return new UserWithdrawAccountResource($userWithdrawAccount);
    }

    public function update(UpsertUserWithdrawAccountRequest $request, $id)
    {
        $user = Auth::user();
        $request->merge(['user_withdraw_account_id' => $request->route('account')]);
        $request->validate(['user_withdraw_account_id' => 'exists:user_withdraw_accounts,id']);

        $name = $request->get('name');
        $phone = $request->get('phone');
        $accountNumber = $request->get('account_number');
        $bankAddress = $request->get('bank_address');
        $bankName = $request->get('bank_name');
        $comment = $request->get('comment');

        $userWithdrawAccount = $this->userWithdrawAccountService->upsert(
            $user->id,
            $id,
            $name,
            $phone,
            $accountNumber,
            $bankAddress,
            $bankName,
            $comment
        );

        return new UserWithdrawAccountResource($userWithdrawAccount);
    }

    public function destroy(Request $request, int $id)
    {
        $request->merge(['user_withdraw_account_id' => $request->route('account')]);
        $request->validate(['user_withdraw_account_id' => 'exists:user_withdraw_accounts,id']);

        $user = Auth::user();
        $userWithdrawAccount = $this->userWithdrawAccountService->getUserWithdrawAccountsQuery($user->id, $id)->first();
        return $userWithdrawAccount->delete();
    }


}
