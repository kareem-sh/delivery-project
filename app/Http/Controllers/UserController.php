<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateUserRequest;
use Exception;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    use AuthorizesRequests;
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }
    public function index()
    {
        try {
        $this->authorize('view', User::class);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return User::paginate(25);
    }
    public function store(CreateUserRequest $request)
    {
        $this->authorize('create',User::class);
        $data = $request->validated();
        $user = User::create($data);
        return response()->json([
            'message' => 'User created successfully',
            'User' => $user,
        ]);
    }
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        return response()->json([
            'message' => 'User returned successfully',
            'user' => $user
        ]);
    }
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $this->authorize('update', $user);

        $data = $request->validated();
        $user->update($data);

        return response()->json(['message' => 'User updated successfully.', 'user' => $user]);
    }


    //  // Check if phone number is being updated
    //  if (isset($data['phone_number']) && $data['phone_number'] !== $user->phone_number) {
    //      // Send OTP to the new phone number using Twilio service
    //      $newPhoneNumber = $data['phone_number'];
    //      $this->twilioService->sendOtp($newPhoneNumber);

    //      // Store the new phone number temporarily in the database (you might need a column like new_phone_number for this)
    //      $user->new_phone_number = $newPhoneNumber;
    //      $user->verification_code = null; // Reset the verification code (new one will be sent)
    //      $user->verification_code_expiry = null; // Reset the expiry (new one will be set)
    //  }

    // Update other fields


    //  // If phone number update was triggered, don't save the user yet, wait for OTP verification
    //  if (isset($data['phone_number']) && $data['phone_number'] !== $user->phone_number) {
    //      $user->save(); // Save the user with the new phone number temporarily
    //      return response()->json(['message' => 'OTP sent to the new phone number. Please verify it.']);
    //  }





    // public function verifyPhoneNumberChange(Request $request)
    // {
    //     $request->validate([
    //         'code' => 'required|string', // OTP sent to the user
    //     ]);

    //     // Retrieve the authenticated user
    //     $user = User::find($request->input('id'));
    //     if(!$user){
    //         return response()->json(['message' => 'User Not found.'], 404);
    //     }
    //     // Check if the user has a pending phone number change request
    //     if (!$user->verification_code || !$user->verification_code_expiry) {
    //         return response()->json(['message' => 'No phone number change request found.'], 400);
    //     }

    //     // Check if the OTP has expired
    //     if (now()->greaterThan($user->verification_code_expiry)) {
    //         return response()->json(['message' => 'OTP has expired.'], 401);
    //     }

    //     // Verify the OTP using your Twilio service
    //     $isVerified = $this->twilioService->verifyOtp($user->phone_number, $request->code);

    //     if (!$isVerified) {
    //         return response()->json(['message' => 'Invalid OTP.'], 401);
    //     }

    //     // Update the user's phone number with the new one
    //     $user->phone_number = $user->new_phone_number; // Assuming you have `new_phone_number` in the DB
    //     $user->verification_code = null; // Clear the OTP from the database
    //     $user->verification_code_expiry = null; // Clear OTP expiry from the database
    //     $user->save();

    //     return response()->json(['message' => 'Phone number updated successfully.', 'user' => $user]);
    // }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $this->authorize('delete',$user);
        $user->tokens->each->delete();
        $user->delete();
        return response()->json([
            'message' => 'user deleted successfully'
        ], 200);
    }
}
