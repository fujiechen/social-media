<?php

namespace App\Http\Middleware;

use App\Dtos\UserDto;
use App\Models\Role;
use App\Models\User;
use App\Services\UserPayoutService;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UnionAuthMiddleware
{
    private UserService $userService;
    private UserPayoutService $userPayoutService;

    public function __construct(
        UserService $userService,
        UserPayoutService $userPayoutService
    ) {
        $this->userService = $userService;
        $this->userPayoutService = $userPayoutService;
    }

    public function handle(Request $request, Closure $next)
    {

        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();
            $accessToken = Str::replace('Bearer ', '', $request->header('Authorization'));
            $userData = $payload->toArray()['user'];

            $userId = $userData['id'];

            /**
             * @var User $user
             */
            $user = User::find($userId);

            if (!$user) {
                $userShareId = $userData['extras']['user_share_id'] ?? null;
                //create role visitor
                $user = $this->userService->createUser(new UserDto([
                    'userId' => $userData['id'],
                    'accessToken' => $accessToken,
                    'username' => $userData['username'],
                    'password' => $userData['password'],
                    'nickname' => $userData['nickname'],
                    'email' => $userData['email'],
                    'userShareId' => $userShareId
                ]));
                $this->userPayoutService->completeNewUserPayout($user->id);
            } else { // sync user with JWT token
                if ($user->access_token != $accessToken) {
                    $this->userService->updateUserAuth(new UserDto([
                        'userId' => $userData['id'],
                        'accessToken' => $accessToken,
                        'username' => $userData['username'],
                        'password' => $userData['password'],
                        'nickname' => $userData['nickname'],
                        'email' => $userData['email'],
                        'roleIds' => [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID],
                    ]));
                }
            }

            Auth::login($user);
            $request->merge(['user' => $user]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}

