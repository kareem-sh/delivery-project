<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderItemResource;
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
    /**
     * Store a new order or cart.
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
    
        // Create a new order (this will always create a new order when called)
        $order = Order::create([
            'user_id' => $validated['user_id'],
            'order_status' => 'cart',
            'total_price' => 0,
        ]);
    
        $totalPrice = 0;
    
        foreach ($validated['order_items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
    
            // Calculate the effective price (discounted price)
            $effectivePrice = $product->getEffectivePriceAttribute(); // Price with discount
    
            // Check stock availability
            if ($product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $product->name,
                ], 400);
            }
    
            $storeId = $product->store_id;
    
            // Check if there's already a suborder for this store in the current order
            $subOrder = $order->subOrders()->where('store_id', $storeId)->first();
    
            if (!$subOrder) {
                // Create a new suborder if none exists for this store
                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'store_id' => $storeId,
                    'sub_total' => 0, // Initially set the sub-total to 0
                    'order_status' => 'cart',
                ]);
            }
    
            // Create the order item and store only the effective price
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'sub_order_id' => $subOrder->id,
                'quantity' => $item['quantity'],
                'price' => $effectivePrice, // Store effective price (discounted price)
            ]);
    
            // Update suborder totals using the effective price (discounted price)
            $subOrder->sub_total += $effectivePrice * $item['quantity'];
            $subOrder->save();
    
            // Update the order's total price
            $totalPrice += $effectivePrice * $item['quantity'];
        }
    
        // Update the order's total price
        $order->update(['total_price' => $totalPrice]);
        $order_details = $order->orderItems;
        // Return the order with order details (items)
        return response()->json([
            'order_details' => new OrderResource($order),
        ], 201);
    }
    
    /**
     * Show an order with its sub-orders and items.
     */
    public function show($id)
    {
        $order = Order::with(['orderItems.product'])->findOrFail($id);
        try{
            // $this->authorize('view', $order);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }
        
        return response()->json(["order_details" => new OrderResource($order)]);
    }

    public function getOrderItems($id)
    {
        $order =  Order::with(['orderItems.product'])->findOrFail($id);
        $order_details = Order::findOrFail($id);
        try{
            // $this->authorize('view', $order);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }
        return response()->json([
            'order_details' => $order_details,
            'order_items' => OrderItemResource::collection($order->orderItems),
        ]);
    }

    /**
     * Update the order (add items, remove items, or update item quantities).
     */
    public function update(UpdateOrderRequest $request, $id)
    {
        // Validate the request
        $validated = $request->validated();
    
        // Find the order along with its sub-orders and items
        $order = Order::with('subOrders.orderItems')->findOrFail($id);
        try{
            $this->authorize('update', $order);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }
        // Ensure the order is in a modifiable status
        if (!in_array($order->order_status, ['cart', 'pending'])) {
            return response()->json(['message' => 'Only cart or pending orders can be updated.'], 400);
        }
    
        $newOrderItems = collect($validated['order_items']);
        $existingOrderItems = $order->subOrders->flatMap->orderItems;
    
        $totalPrice = 0;
    
        // Step 1: Update or add order items
        foreach ($newOrderItems as $item) {
            $product = Product::findOrFail($item['product_id']);
    
            // Find or create the corresponding sub-order
            $storeId = $product->store_id;
            $subOrder = SubOrder::firstOrCreate(
                ['order_id' => $order->id, 'store_id' => $storeId],
                ['sub_total' => 0, 'order_status' => $order->order_status]
            );
    
            // Check if the order item already exists
            $orderItem = $subOrder->orderItems()->where('product_id', $item['product_id'])->first();
    
            $effectivePrice = $product->getEffectivePriceAttribute();
    
            if ($orderItem) {
                // Calculate the difference in quantity
                $quantityDiff = $item['quantity'] - $orderItem->quantity;
    
                // Adjust product stock if the order is in pending status
                if ($order->order_status === 'pending') {
                    if ($quantityDiff > 0) { // Ordering more items
                        if ($product->stock_quantity < $quantityDiff) {
                            return response()->json([
                                'message' => 'Insufficient stock for product: ' . $product->name,
                            ], 400);
                        }
                        $product->stock_quantity -= $quantityDiff;
                    } elseif ($quantityDiff < 0) { // Returning items
                        $product->stock_quantity += abs($quantityDiff);
                    }
                    $product->save();
                }
    
                // Update the existing order item's quantity and price
                $subOrder->sub_total -= $orderItem->price * $orderItem->quantity;
    
                $orderItem->update([
                    'quantity' => $item['quantity'],
                    'price' => $effectivePrice,
                ]);
            } else {
                // Create a new order item
                $orderItem = $subOrder->orderItems()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $effectivePrice,
                ]);
    
                // Adjust product stock if the order is in pending status
                if ($order->order_status === 'pending') {
                    if ($product->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'message' => 'Insufficient stock for product: ' . $product->name,
                        ], 400);
                    }
                    $product->stock_quantity -= $item['quantity'];
                    $product->save();
                }
            }
    
            // Update the sub-order total
            $subOrder->sub_total += $effectivePrice * $item['quantity'];
            $subOrder->save();
    
            // Add to the order's total price
            $totalPrice += $effectivePrice * $item['quantity'];
        }
    
        // Step 2: Remove order items that are no longer in the request
        foreach ($existingOrderItems as $existingItem) {
            if (!$newOrderItems->pluck('product_id')->contains($existingItem->product_id)) {
                $subOrder = $existingItem->subOrder;
    
                if ($order->order_status === 'pending') {
                    // Adjust product stock to add back the returned stock
                    $product = $existingItem->product;
                    $product->stock_quantity += $existingItem->quantity;
                    $product->save();
                }
    
                $subOrder->sub_total -= $existingItem->price * $existingItem->quantity;
                $existingItem->delete();
    
                // Delete subOrder if it has no items left
                if ($subOrder->orderItems()->count() === 0) {
                    $subOrder->delete();
                } else {
                    $subOrder->save();
                }
            }
        }
    
        // Step 3: Update the total price of the order
        $order->update(['total_price' => $totalPrice]);
        $order_details = $order->orderItems;
    
        return response()->json([
            'message' => 'Order updated successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }
    


    /**
     * Cancel a sub-order.
     */
    public function cancelSubOrder($id)
    {
        $subOrder = SubOrder::findOrFail($id);
        $this->authorize('update', $subOrder->order);

        if ($subOrder->order_status !== 'cart') {
            return response()->json(['message' => 'Only cart sub-orders can be canceled.'], 400);
        }

        $subOrder->update(['order_status' => 'canceled']);

        $mainOrder = $subOrder->order;
        $mainOrder->total_price -= $subOrder->sub_total;
        $mainOrder->save();

        $activeSubOrders = $mainOrder->subOrders()->where('order_status', '!=', 'canceled')->count();

        if ($activeSubOrders === 0) {
            $mainOrder->update(['order_status' => 'canceled']);
        }

        return response()->json([
            'message' => 'Sub-order canceled successfully.',
            'main_order' => $mainOrder,
            'sub_order' => $subOrder,
        ], 200);
    }

    /**
     * Cancel the entire order.
     */
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        try{
            $this->authorize('delete', $order);
        }catch(Exception $e){
           return response()->json(['message' => 'Unauthorized action']);
        }
        

        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be canceled.'], 400);
        }

        $order->update(['order_status' => 'canceled']);

        foreach ($order->subOrders as $subOrder) {
            $subOrder->update(['order_status' => 'canceled']);
        }

        return response()->json([
            'message' => 'Order canceled successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }
    // In OrderController

    /**
     * Submit the order, changing its status from cart to pending.
     */
    public function submit($id)
{
    // Find the order with its related suborders and order items
    $order = Order::with('subOrders.orderItems.product')->findOrFail($id);
    try{
        // $this->authorize('update', $order);
    }catch(Exception $e){
        return response()->json([
            'message' => 'Unauthorized action'
        ]);
    }
    

    // Ensure the order is still in the 'cart' status
    if ($order->order_status !== 'cart') {
        return response()->json(['message' => 'Only cart orders can be submitted.'], 400);
    }

    // Update the order status to 'pending'
    $order->update(['order_status' => 'pending']);

    // Loop through suborders and order items to update product stock quantity
    foreach ($order->subOrders as $subOrder) {
        foreach ($subOrder->orderItems as $orderItem) {
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

        // Update suborder status to 'pending'
        $subOrder->update(['order_status' => 'pending']);
    }

    // Return the updated order data
    return response()->json([
        'message' => 'Order submitted successfully.',
        'order' => new OrderResource($order),
    ], 200);
}


    public function getCart()
    {   
        // Use Auth::user() to get the currently authenticated user
        $user = Auth::user();
        // return $user;
        // Get all orders with status 'cart' for the authenticated user
        $cartOrders = Order::where('user_id', $user->id)
                           ->where('order_status', 'cart')
                           ->with('orderItems') 
                           ->get();
        // return $cartOrders;
                           if(!$cartOrders){
            return response()->json([
                "message" => "The cart is empty"
            ]);
        }
        // // Return the cart orders with total price as a JSON response
        return response()->json(["order_details" => OrderResource::collection($cartOrders)]);
    }
  
}
