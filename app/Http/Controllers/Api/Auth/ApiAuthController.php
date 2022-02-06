<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiAuthController extends Controller
{
    use ApiResponse;

    /**
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return $this->error('Credentials does not match', 401);
        }

        session()->regenerate();
        $user = $request->user();

        return $this->success([
            'user' => new UserResource($user),
            'token' => User::assignSanctumToken($user)
        ]);
    }

    /**
     * @return string[]
     */
    public function logout(): array
    {
        request()->user()->currentAccessToken()->delete();

        return [
            'message' => 'Current Token Revoked'
        ];
    }

    /**
     * @return string[]
     */
    public function logoutAllUserDevices(): array
    {
        request()->user()->tokens()->delete();

        return [
            'message' => 'You have been logged out of all devices.'
        ];
    }
}
