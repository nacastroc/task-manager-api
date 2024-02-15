<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    /**
     * Handle the email verification request.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $id = $request->route('id');
        $hash = $request->route('hash');
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if the hash matches
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return response()->json(['message' => 'Email verified successfully'], 200);
        }

        return response()->json(['message' => 'Unable to verify email'], 500);
    }
}
