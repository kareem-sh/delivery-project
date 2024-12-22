<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny',User::class);
        $orders = Order::where('user_id',request()->user())
        ->where('order_status', '!=', 'cart')
        ->get()
        ->groupBy(function($date) {
            return Carbon::parse($date->order_date)->format('Y-m-d H:i');
        });

    return response()->json($orders);
    }
    public function store(Request $request)
    {
        $this->authorize('create',User::class);
        $data=$request->validate([
            "product_id"=>"required",
        ]);
        $product=Product::find($request->product_id);
        if($product->discount_value&&$product->discount_start<=now()&&$product->discount_end>now())
            $price=$product->price-($product->discount_value*$product->price/100);
        else
            $price=$product->price;
        Order::create([
            ...$data,
            'user_id'=>request()->user(),
            'quantity'=>1,
            'price'=>$price
        ]);
    }
    public function show(Order $order)
    {
        $this->authorize('view',[User::class,$order]);
        return $order;
    }
    public function update(Request $request, Order $order)
    {
        $this->authorize('update',[User::class,$order]);
        $data=$request->validate([
            "quantity"=>"required"
        ]);
        $product=Product::find($order->product_id);
        $max_quantity=$product->stock_quantity;
        if($max_quantity<$request->quantity)
            return response()->json([
                'message'=>'the quantity is not available'
            ]);
        if($product->discount_value&&$product->discount_start<=now()&&$product->discount_end>now())
            $price=$product->price-($product->discount_value*$product->price/100);
        else
            $price=$product->price;
        $price=$request->quantity*$price;
        $order->update([
            ...$data,
            "price"=>$price
        ]);
    }
    public function destroy(Order $order)
    {
        $this->authorize('delete',[User::class,$order]);
        if($order->order_status=="cart")
        {
            $order->delete();
        }
        else
        {
            return response()->json([
                'message'=>'you can not delete this order'
            ]);
        }
    }
    public function submit($id)
    {
        $order=Order::find($id);
        if($order->order_status!="cart")
        {
            return response()->json([
                'message'=>'the order is submited'
            ]);
        }
        $order->update([
            'order_status'=>"preparing",
            'order_date'=>now()
        ]);
        $product=Product::find($order->product_id);
        $stock_quantity=$product->stock_quantity-$order->quantity;
        $product->update(["stock_quantity"=>$stock_quantity]);
        if($stock_quantity==0)
            $product->delete();
    }
    public function cart()
    {
        $orders=request()->user()->orders->where('order_status',"cart");
        return $orders;
    }

}
