<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ArProductResource;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use Exception;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Log;
use Illuminate\Http\Client\ResponseSequence;

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
            $this->authorize('view', Auth::user());
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        return UserResource::collection(User::all());
    }

    public function store(CreateUserRequest $request)
    {
        try {
            $this->authorize('create', User::class);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }
        $data = $request->validated();
        $user = User::create($data);
        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user)
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
            'user' => new UserResource($user)
        ]);
    }


    public function update(UpdateUserRequest $request, $id)
    {
        // Find the user by ID
        $user = User::find($id);

        // If the user is not found, return a 404 response
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            // Check if the user is authorized to update
            $this->authorize('update', $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        // Validate and prepare the data
        $data = $request->validated();

        // Ensure only admin can update the role
        if ($request->has('role') && Auth::user()->role != 'admin') {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        // Handle image upload (if provided)
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            // Store the new image and get the path
            $imagePath = $request->file('image')->store('users', 'public');

            // Update the 'image' field with the new path
            $data['image'] = $imagePath;
        }

        // Update the user with the validated data
        $user->update($data);

        // Return a success response with the updated user data
        return response()->json([
            'message' => 'User updated successfully.',
            'user' => new UserResource($user),
        ]);
    }




    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $this->authorize('delete', $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }
        $user->tokens->each->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
    public function toggle_favorites(CreateFavoriteRequest $request)
    {
        try {
            $this->authorize('update', Auth::user());
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }
        $product_ids = $request->input('product_id');
        Auth::user()->toggleToFavorites($product_ids);
        return response()->json(['message' => 'Favorites toggled successfully'], 200);
    }


    public function favorites()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found. Please check your authentication token.'], 401);
        }

        try {
            $this->authorize('update', $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        if (!$user->favorites) {
            return response()->json([
                'message' => "There are no products in favorite list"
            ]);
        }
        if (Auth::user()->lang == "en") {
            return response()->json([
                "favorites" => ProductResource::collection($user->favorites)
            ]);
        } else {
            return response()->json([
                "favorites" => ArProductResource::collection($user->favorites)
            ]);
        }
    }

    public function getUserOrders(Request $request)
    {
        $user = Auth::user();
        try {
            $this->authorize('update', $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }
        $status = $request->query('status');
        $sort = $request->query('sort', 'newest');

        $query = Order::where('user_id', $user->id);

        // Exclude 'cart' status
        if ($status && $status !== 'cart' && $status !== 'canceled') {
            $query->where('order_status', $status);
        }

        // Sorting
        if ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $orders = $query->whereNotIn('order_status', ['cart', 'canceled'])
            ->with('orderItems.product')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders available.'
            ]);
        }
        return response()->json([
            'orders' => OrderResource::collection($orders)
        ]);
    }

    public function getUserNotifications()
    {
        $user = Auth::user();
        $user->markAllNotificationsAsRead();
        return response()->json([
            'Notifications' => NotificationResource::collection($user->notifications),
        ], 200);
    }
}
