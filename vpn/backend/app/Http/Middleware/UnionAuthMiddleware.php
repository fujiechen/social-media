<?php

namespace App\Http\Middleware;

use App\Dtos\UserDto;
use App\Models\Role;
use App\Models\User;
use App\Models\UserShare;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UnionAuthMiddleware
{
    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
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
                $userShare = UserShare::find($userShareId);
                if (empty($userShare)) {
                    $userShareId = null;
                }

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

            } else { // sync user with JWT token
                if ($user->access_token != $accessToken) {
                    $oldToken = $user->access_token;
                    $this->userService->updateUserAuth(new UserDto([
                        'userId' => $userData['id'],
                        'accessToken' => $accessToken,
                        'username' => $userData['username'],
                        'password' => $userData['password'],
                        'nickname' => $userData['nickname'],
                        'email' => $userData['email'],
                        'roleIds' => [Role::ROLE_USER_ID],
                    ]));

                    //expire old token
                    $oldTokenExpired = $userData['extras']['old_token_expired'] ?? null;
                    if ($oldTokenExpired) {
                        JWTAuth::setToken($oldToken)->invalidate();
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Auth::login($user);
        $request->merge(['user' => $user]);

        return $next($request);
    }
}

