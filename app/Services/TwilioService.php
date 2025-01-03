<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use App\Models\User;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        // $sid = env('TWILIO_SID');
        // $token = env('TWILIO_AUTH_TOKEN');
        // $this->twilio = new Client($sid, $token);
    }

    /**
     * Send OTP to the user's phone and store it with an expiry in the database.
     * @param string $phone
     * @return string
     */
    public function sendOtp(string $phone)
    {
        try {
            $verification = $this->twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verifications
                ->create($phone, 'sms');

            // Save OTP and expiry to the database
            $user = User::where('phone_number', $phone)->first();
            if ($user) {
                $user->verification_code = $verification->sid;
                $user->verification_code_expiry = now()->addMinutes(2); // 2-minute expiry
                $user->save();
            }

            return $verification->sid;
        } catch (TwilioException $e) {
            return $e->getMessage(); // Return error message
        }
    }

    /**
     * Verify OTP entered by the user.
     * @param string $phone
     * @param string $code
     * @return bool
     */
    public function verifyOtp(string $phone, string $code): bool
    {
        $user = User::where('phone_number', $phone)->first();

        // Ensure user exists
        if (!$user) {
            return false;
        }

        // Check if OTP has expired
        if (now()->greaterThan($user->verification_code_expiry)) {
            return false; // OTP expired
        }

        // Verify OTP with Twilio
        try {
            $verificationCheck = $this->twilio->verify->v2->services(env('TWILIO_VERIFY_SID'))
                ->verificationChecks
                ->create([
                    'to' => $phone,
                    'code' => $code
                ]);

            if ($verificationCheck->status === 'approved') {
                $user->is_verified = true;
                $user->verification_code = null;
                $user->verification_code_expiry = null;
                $user->save();
                return true;
            }

            return false;
        } catch (TwilioException $e) {
            return false;
        }
    }
}
