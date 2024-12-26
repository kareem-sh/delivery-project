<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use Exception;
use App\Models\Order;

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
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        return UserResource::collection(User::paginate(25));
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
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $this->authorize('update', $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }

        $data = $request->validated();
        if ($request->has('role') && Auth::user()->role != 'admin') {
            return response()->json(['message' => 'This action is unauthorized.'], 401);
        }
        $user->update($data);

        return response()->json(['message' => 'User updated successfully.', 'user' => new UserResource($user)]);
    }

    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $this->authorize('delete', $user);
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
        
        if(!$user->favorites){
            return response()->json([
                'message' => "There are no products in favorite list"
            ]);
        }

        return response()->json([
            "favorites" =>FavoriteResource::collection($user->favorites)
        ]) ;
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
    
}
