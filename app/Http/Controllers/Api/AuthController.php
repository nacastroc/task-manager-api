<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailVerificationNotification;
use App\Models\User;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

/**
 * Class AuthController
 *
 * This class handles authentication requests.
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function register(Request $request, ValidatorService $validatorService)
    {
        $validatorService->validateUserRegister($request);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('token-name')->plainTextToken;

        // Send email verification notification
        dispatch(new SendEmailVerificationNotification($user));

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Log in a user.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request, ValidatorService $validatorService)
    {
        $validatorService->validateUserLogin($request);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => config('constants.messages.http_401_invalid_credentials'),
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('token-name')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Log out the current user.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => config('constants.messages.http_200_logout')], 200);
    }
}
