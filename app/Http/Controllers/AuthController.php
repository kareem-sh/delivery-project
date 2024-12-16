<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwilioService;

class AuthController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function handleRequest(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $fcmToken = $request->fcm_token;

        $user = User::where('fcm_token', $fcmToken)->first();

        if ($user) {
            return $this->handleExistingUser($user);
        }

        return response()->json([
            'message' => 'Phone number required.',
            'require_phone_number' => true,
        ]);
    }

    private function handleExistingUser($user)
    {
        $sanctumToken = $user->tokens->first();

        if ($user->is_verified && $sanctumToken) {
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $sanctumToken->plainTextToken,
                'require_phone_number' => false,
            ]);
        }

        $user->is_verified = false;
        $this->twilioService->sendOtp($user->phone_number);
        $user->save();

        return response()->json([
            'message' => 'Verification required. OTP sent.',
            'require_phone_number' => false,
            'user' => $user,
        ]);
    }

    public function handlePhoneNumber(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $fcmToken = $request->fcm_token;
        $phoneNumber = $request->phone_number;

        $user = User::where('phone_number', $phoneNumber)->first();

        if ($user) {
            $user->fcm_token = $fcmToken;
            $user->is_verified = false;
            $this->twilioService->sendOtp($phoneNumber);
            $user->tokens->each->delete();
            $user->save();

            return response()->json([
                'message' => 'Verification required. OTP sent.',
                'user' => $user,
            ]);
        }

        return $this->createNewUser($fcmToken, $phoneNumber);
    }

    private function createNewUser($fcmToken, $phoneNumber)
    {
        $newUser = User::create([
            'phone_number' => $phoneNumber,
            'fcm_token' => $fcmToken,
            'is_verified' => false,
        ]);

        $this->twilioService->sendOtp($phoneNumber);

        return response()->json([
            'message' => 'User created. Verification required. OTP sent.',
            'user' => $newUser,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'code' => 'required|string',
        ]);

        $phoneNumber = $request->phone_number;
        $otpCode = $request->code;

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check OTP validity
        if (now()->greaterThan($user->verification_code_expiry)) {
            return response()->json(['message' => 'OTP has expired.'], 401);
        }

        $isVerified = $this->twilioService->verifyOtp($phoneNumber, $otpCode);

        if (!$isVerified) {
            return response()->json(['message' => 'Invalid OTP.'], 401);
        }

        $user->is_verified = true;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        $isNewUser = $this->requiresAdditionalData($user);

        return response()->json([
            'message' => $isNewUser
                ? 'Verification successful. Please complete your profile.'
                : 'Verification successful. Welcome back!',
            'token' => $token,
            'user' => $user,
            'is_new_user' => $isNewUser,
        ]);
    }

    private function requiresAdditionalData($user)
    {
        return $user->full_name == null;
    }
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $phoneNumber = $request->phone_number;

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the previous OTP is still valid
        if ($user->verification_code_expiry && now()->lessThan($user->verification_code_expiry)) {
            return response()->json([
                'message' => 'Please wait until the current OTP expires before requesting a new one.',
                'expiry_time' => $user->verification_code_expiry,
            ], 429); // 429 Too Many Requests
        }

        // Resend a new OTP
        $this->twilioService->sendOtp($phoneNumber);

        return response()->json([
            'message' => 'A new OTP has been sent to your phone.',
            'user' => $user,
        ]);
    }
}
