<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderItemResource;
use App\Notifications\OrderStatusChanged;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\User;
use Exception;
use Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuthorizesRequests;


    public function index()
    {
        return Order::with('ordeItems')->get();
    }

    /**
     * Show an order with its sub-orders and items.
     */
    public function show($id)
    {
        $order = Order::with(['orderItems.product'])->findOrFail($id);
        try {
            $this->authorize('view', $order);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }

        return response()->json(["order_details" => new OrderResource($order)]);
    }


    /**
     * Cancel the entire order.
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        try {
            $this->authorize('delete', $order);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthorized action']);
        }


        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be canceled.'], 400);
        }

        $order->update(['order_status' => 'canceled']);

        $user = $order->user;
        $user->notify(new OrderStatusChanged($order, 'canceled'));

        return response()->json([
            'message' => 'Order canceled successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }
    // In OrderController

    /**
     * Submit the order, changing its status from cart to pending.
     */
    public function submitCart()
    {
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Find the order with order_status 'cart' and ensure it belongs to the authenticated user
        $order = Order::with(['orderItems.product'])
            ->where('user_id', $userId)
            ->where('order_status', 'cart')
            ->first();

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'No cart order found.'], 404);
        }

        try {
            // Check if the user is authorized to update the order
            $this->authorize('update', $order);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Update the order status to 'pending'
        $order->update(['order_status' => 'pending']);

        // Loop through order items to update product stock quantity
        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;

            // Check if the product stock is sufficient before reducing the quantity
            if ($product->stock_quantity < $orderItem->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $product->name,
                ], 400);
            }

            // Subtract the quantity ordered from the product's stock
            $product->stock_quantity -= $orderItem->quantity;
            $product->save();
        }
        $user = $order->user;
        $user->notify(new OrderStatusChanged($order, 'created'));
        // Return the updated order data
        return response()->json([
            'message' => 'Order submitted successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }



    public function getCart()
    {
        $user = Auth::user();
        $cartOrders = Order::where('user_id', $user->id)
            ->where('order_status', 'cart')
            ->with('orderItems')
            ->get();
        // return $cartOrders;
        if (!$cartOrders) {
            return response()->json([
                "message" => "The cart is empty"
            ]);
        }
        // // Return the cart orders with total price as a JSON response
        return response()->json(["order_details" => OrderResource::collection($cartOrders)]);
    }


    // Function to add items to the cart
    public function addToCart(StoreOrderRequest $request)
    {

        $userId = auth()->id();
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);
        $quantity = 1;
        if (!$product->hasSufficientStock($quantity)) {
            return response()->json([
                'message' => 'Insufficient stock for the selected product.',
            ], 400);
        }

        $order = Order::where('user_id', $userId)
            ->where('order_status', 'cart')
            ->first();

        if (!$order) {
            $order = Order::create([
                'user_id' => $userId,
                'order_status' => 'cart',
                'total_price' => 0,
            ]);
        }

        // Check if the product is already in the cart
        $existingOrderItem = $order->orderItems()
            ->where('product_id', $product->id)
            ->first();

        if ($existingOrderItem) {
            return response()->json([
                'message' => 'Item already exists in the cart.',
            ], 400);
        }

        // Add a new item with a quantity of 1
        $effectivePrice = $product->effective_price;

        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $effectivePrice,
        ]);

        $order->update([
            'total_price' => $order->total_price + ($effectivePrice * $quantity),
        ]);
        $order_details = $order->orderItems;
        return response()->json([
            'message' => 'Item added to cart successfully.',
            'order_details' => new OrderResource($order),
        ], 200);
    }

    public function removeFromCart(Request $request)
    {
        $userId = auth()->id();
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $order = Order::where('user_id', $userId)
            ->where('order_status', 'cart')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'No active cart found.',
            ], 404);
        }

        $orderItem = $order->orderItems()
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$orderItem) {
            return response()->json([
                'message' => 'Product not found in the cart.',
            ], 404);
        }

        // Update the total price
        $order->update([
            'total_price' => $order->total_price - ($orderItem->price * $orderItem->quantity),
        ]);

        // Remove the item
        $orderItem->delete();

        // If the cart is now empty, delete the order
        if ($order->orderItems()->count() === 0) {
            $order->delete();

            return response()->json([
                'message' => 'Cart is now empty and has been deleted.',
            ], 200);
        }
        $order_details = $order->orderItems;
        return response()->json([
            'message' => 'Item removed from cart successfully.',
            'order_details' => new OrderResource($order),
        ], 200);
    }

    public function updateCart(UpdateOrderRequest $request)
    {

        $userId = auth()->id();
        $validated = $request->validated();

        $incomingItems = collect($validated['order_items']);
        $order = Order::where('user_id', $userId)
            ->where('order_status', 'cart')
            ->first();


        try {
            $this->authorize('update', $order);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }

        if (!$order) {
            return response()->json([
                'message' => 'No active cart found.',
            ], 404);
        }

        $existingItems = $order->orderItems()->get();
        $totalPrice = 0;

        foreach ($incomingItems as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                // If product is not found, return an error message but continue with the loop
                return response()->json([
                    'message' => "Product with ID {$item['product_id']} does not exist.",
                ], 404);
            }

            // Check stock availability
            if (!$product->hasSufficientStock($item['quantity'])) {
                return response()->json([
                    'message' => "Insufficient stock for product ID {$item['product_id']}.",
                ], 400);
            }

            $orderItem = $existingItems->where('product_id', $item['product_id'])->first();
            $effectivePrice = $product->effective_price;

            if ($orderItem) {
                // Update the existing item
                $orderItem->update([
                    'quantity' => $item['quantity'],
                    'price' => $effectivePrice,
                ]);
            } else {
                // If the item is not in the cart, return a message but continue with the loop
                return response()->json([
                    'message' => "Product with ID {$item['product_id']} does not exist in the cart.",
                ], 404);
            }

            // Calculate total price based on updated quantities and prices
            $totalPrice += $effectivePrice * $item['quantity'];
        }


        foreach ($existingItems as $orderItem) {
            $totalPrice += $orderItem->price * $orderItem->quantity;
        }


        // Update the order's total price
        $order->update(['total_price' => $totalPrice]);
        $order_details = $order->orderItems;
        return response()->json([
            'message' => 'Cart updated successfully.',
            'order_details' => new OrderResource($order),
        ], 200);
    }




    public function updatePendingOrder(UpdateOrderRequest $request)
    {
        $userId = auth()->id(); // Assuming the user is authenticated
        $validated = $request->validated();

        $incomingItems = collect($validated['order_items']);
        $order = Order::where('user_id', $userId)
            ->where('order_status', 'pending')
            ->first();

        try {
            $this->authorize('update', $order);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }

        if (!$order) {
            return response()->json(['message' => 'No pending order found.'], 404);
        }

        $existingItems = $order->orderItems()->get();
        $totalPrice = 0;

        foreach ($incomingItems as $item) {
            $product = Product::findOrFail($item['product_id']);
            $orderItem = $existingItems->where('product_id', $item['product_id'])->first();

            if ($orderItem) {
                // Update quantity and adjust stock
                $quantityDiff = $item['quantity'] - $orderItem->quantity;

                if ($quantityDiff > 0 && $product->stock_quantity < $quantityDiff) {
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $product->name,
                    ], 400);
                }

                $product->stock_quantity -= $quantityDiff;
                $product->save();

                $orderItem->update([
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            } else {
                // Add new item
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $product->name,
                    ], 400);
                }

                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->stock_quantity -= $item['quantity'];
                $product->save();
            }

            $totalPrice += $product->price * $item['quantity'];
        }

        // Remove items not in the incoming list and adjust stock
        $existingItems->whereNotIn('product_id', $incomingItems->pluck('product_id')->toArray())
            ->each(function ($orderItem) {
                $product = Product::findOrFail($orderItem->product_id);
                $product->stock_quantity += $orderItem->quantity;
                $product->save();

                $orderItem->delete();
            });

        $order->update(['total_price' => $totalPrice]);
        $user = $order->user;
        $user->notify(new OrderStatusChanged($order, 'updated'));
        $order_details = $order->orderItems;
        return response()->json([
            'message' => 'Pending order updated successfully.',
            'order_details' => new OrderResource($order),
        ], 200);
    }
}
