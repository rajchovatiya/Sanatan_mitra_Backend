<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Otp\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function sendOtp(SendOtpRequest $request, OtpService $otp): JsonResponse
    {
        $code = $otp->send($request->validated('mobile'));

        $body = [
            'message' => 'OTP sent.',
            'expires_in' => config('sanatan.otp.ttl'),
            // How many digits the app should ask for.
            'length' => config('sanatan.otp.length'),
        ];

        // Lets the app be tested without SMS. Never in production.
        if (config('sanatan.otp.expose_code') && ! app()->isProduction()) {
            $body['debug_code'] = $code;
        }

        return response()->json($body);
    }

    /** Checks the code, creates the account on first login and returns a token. */
    public function verifyOtp(VerifyOtpRequest $request, OtpService $otp): JsonResponse
    {
        $data = $request->validated();

        if (! $otp->verify($data['mobile'], $data['code'])) {
            throw ValidationException::withMessages(['code' => 'The code is wrong or has expired.']);
        }

        $user = User::firstOrCreate(['mobile' => $data['mobile']]);

        if ($user->isBlocked()) {
            return response()->json(['message' => 'This account is blocked. Please contact support.'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token' => $user->createToken($data['device_name'] ?? 'app')->plainTextToken,
            'is_new' => $user->wasRecentlyCreated,
            'user' => new UserResource($user),
        ]);
    }

    /** Ends this phone's session; also stops its notifications when fcm_token is sent. */
    public function logout(Request $request): Response
    {
        $user = $request->user();

        if (is_string($token = $request->input('fcm_token'))) {
            $user->devices()->where('fcm_token', $token)->delete();
        }

        $user->currentAccessToken()->delete();

        return response()->noContent();
    }
}
