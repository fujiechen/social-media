<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $token = JWTAuth::parseToken();
        try {
            $payload = $token->getPayload();
            $accessToken = Str::replace('Bearer ', '', $request->header('Authorization'));
            $userData = $payload->toArray()['user'];

            $userId = $userData['id'];
            /**
             * @var User $user
             */
            $user = User::find($userId);

            if (!$user) {
                $userAgentId = $userData['extras']['user_agent_id'] ?? null;
                $user = $this->userService->create($userId, $accessToken, $userData['nickname'],
                    $userData['email'], $userData['password'], $userData['username'],
                    $userData['language'], [], $userAgentId, $userData['phone'] ?? null, $userData['wechat'] ?? null,
                    $userData['whatsapp'] ?? null, $userData['alipay'] ?? null, $userData['telegram'] ?? null, $userData['facebook'] ?? null);
            } else { // sync user with JWT token
                if ($user->access_token != $accessToken) {
                    $oldToken = $user->access_token;

                    $this->userService->update(
                        $userData['id'],
                        $accessToken,
                        $userData['email'],
                        $userData['password'],
                        $userData['username'],
                        $userData['nickname'],
                        $userData['language'],
                        $userData['phone'] ?? null,
                        $userData['whatsapp'] ?? null,
                        $userData['facebook'] ?? null,
                        $userData['telegram'] ?? null,
                        $userData['wechat'] ?? null,
                        $userData['alipay'] ?? null,
                    );

                    //expire old token
                    $oldTokenExpired = $userData['extras']['old_token_expired'] ?? null;

                    if ($oldTokenExpired) {
                        JWTAuth::setToken($oldToken)->invalidate();
                    }
                }
            }

        } catch (\Exception $e) {
            Log::info('union.auth: invalid token' . $e->getMessage());
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Auth::login($user);
        $request->merge(['user' => $user]);

        return $next($request);
    }
}

