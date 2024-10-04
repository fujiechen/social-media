<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mews\Captcha\Facades\Captcha;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function captcha(): JsonResponse
    {
        $captcha = Captcha::create('mini', true);

        return response()->json([
            'captcha_key' => $captcha['key'], // Unique identifier for the CAPTCHA session
            'captcha_image' => $captcha['img'] // Data URL of the CAPTCHA image
        ]);
    }

    public function login(Request $request): JsonResponse|UserResource
    {
        Validator::make($request->all(), [
            'captcha' => 'required|captcha_api:' . request('captcha_key') . ',mini',
            'username' => 'required|exists:users,username',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('username', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();
        $user->access_token = JWTAuth::fromUser($user);
        $user->save();

        return new UserResource($user);
    }

    public function loginFromApi(Request $request): JsonResponse|UserResource
    {
        Validator::make($request->all(), [
            'api_key' => 'required|exists:api_keys,key',
            'username' => 'required|exists:users,username',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('username', 'password');
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        /**
         * @var User $user
         */
        $user = Auth::user();
        $user->access_token = JWTAuth::fromUser($user);
        $user->save();

        return new UserResource($user);
    }

    public function refreshToken(): UserResource
    {
        $token = JWTAuth::parseToken()->refresh();

        /**
         * @var User $user
         */
        $user = Auth::user();
        $user->access_token = $token;
        $user->save();

        return new UserResource($user);
    }

    /**
     * create user
     *
     * @param Request $request
     * @return UserResource
     */
    public function createUser(Request $request): UserResource
    {
        $validatedData = $request->validate([
            'captcha' => 'required|captcha_api:' . request('captcha_key') . ',mini',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'nickname' => 'required',
            'password' => ['required', 'min:8'],
            'confirm_password' => 'required|same:password',
            'language' => [
                'nullable',
                Rule::in(Language::LANGUAGES),
            ],
            'phone' => 'nullable',
            'wechat' => 'nullable',
            'whatsapp' => 'nullable',
            'alipay' => 'nullable',
            'telegram' => 'nullable',
            'facebook' => 'nullable',
        ]);

        unset($validatedData['captcha']);

        /**
         * @var User $user
         */
        $user = User::create([
            'username' => $validatedData['username'],
            'nickname' => $validatedData['nickname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'language' => $validatedData['language'],
            'phone' => $validatedData['phone'] ?? null,
            'wechat' => $validatedData['wechat'] ?? null,
            'whatsapp' => $validatedData['whatsapp'] ?? null,
            'alipay' => $validatedData['alipay'] ?? null,
            'telegram' => $validatedData['telegram'] ?? null,
            'facebook' => $validatedData['facebook'] ?? null,
            'extras' => array_diff($request->all(), $validatedData)
        ]);

        $token = JWTAuth::fromUser($user);
        $user->access_token = $token;
        $user->save();

        return new UserResource($user);
    }

    /**
     * create user from API
     *
     * @param Request $request
     * @return UserResource
     */
    public function createUserFromApi(Request $request): UserResource
    {
        $validatedData = $request->validate([
            'api_key' => 'required|exists:api_keys,key',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'nickname' => 'required',
            'password' => ['required', 'min:8'],
            'confirm_password' => 'required|same:password',
            'language' => [
                'nullable',
                Rule::in(Language::LANGUAGES),
            ],
            'phone' => 'nullable',
            'wechat' => 'nullable',
            'whatsapp' => 'nullable',
            'alipay' => 'nullable',
            'telegram' => 'nullable',
            'facebook' => 'nullable',
        ]);

        unset($validatedData['captcha']);

        /**
         * @var User $user
         */
        $user = User::create([
            'username' => $validatedData['username'],
            'nickname' => $validatedData['nickname'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'language' => $validatedData['language'],
            'phone' => $validatedData['phone'] ?? null,
            'wechat' => $validatedData['wechat'] ?? null,
            'whatsapp' => $validatedData['whatsapp'] ?? null,
            'alipay' => $validatedData['alipay'] ?? null,
            'telegram' => $validatedData['telegram'] ?? null,
            'facebook' => $validatedData['facebook'] ?? null,
            'extras' => array_diff($request->all(), $validatedData)
        ]);

        $token = JWTAuth::fromUser($user);
        $user->access_token = $token;
        $user->save();

        return new UserResource($user);
    }
}
