<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TwilioService;
use App\Http\Requests\Auth\handlePhoneNumberRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class AuthController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function handlePhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'fcm_token' => 'required|string',
        ]);

        $phoneNumber = $request->phone_number;
        $fcmToken = $request->fcm_token;

        $user = User::where('phone_number', $phoneNumber)->first();

        if ($user) {
            // Update or add the device's FCM token
            $user->devices()->updateOrCreate(
                ['fcm_token' => $fcmToken]
            );

            // Send OTP
            $user->is_verified = false;
            $user->save();
            $this->twilioService->sendOtp($phoneNumber);

            return response()->json([
                'message' => 'OTP sent for verification.',
            ]);
        } else {
            // Create a new user and add the device
            $newUser = User::create([
                'phone_number' => $phoneNumber,
                'is_verified' => false,
            ]);

            $newUser->devices()->create([
                'fcm_token' => $fcmToken,
            ]);

            $this->twilioService->sendOtp($phoneNumber);

            return response()->json([
                'message' => 'OTP sent for verification.',
            ]);
        }
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
            'user' => new UserResource($user),
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
        ]);
    }

    public function logout(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        $user = Auth::user();

        $device = Device::where('user_id', $user->id)
            ->where('fcm_token', $request->fcm_token)
            ->first();

        if ($device) {
            $device->delete();
        }
        $currentToken = auth()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully ']);
    }
}
