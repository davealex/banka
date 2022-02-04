<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApiAuthController extends Controller
{
    use ApiResponse;

    public function register(UserRegisterRequest $request): JsonResponse
    {
        $user = User::createNewUser($request->validated());

        return $this->success([
            'user' => $user,
            'token' => $user->createToken($request->fingerprint())->plainTextToken
        ]);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return $this->error('Credentials does not match', 401);
        }

        $user = $request->user();

        return $this->success([
            'user' => $user,
            'token' => User::assignSanctumToken($user)
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return [
            'message' => 'Current Token Revoked'
        ];
    }

    public function logoutAllUserDevices()
    {
        request()->user()->tokens()->delete();

        return [
            'message' => 'You have been logged out of all devices.'
        ];
    }

    public function user(): JsonResponse
    {
        return $this->success([
            'user' => request()->user()
        ]);
    }
}
