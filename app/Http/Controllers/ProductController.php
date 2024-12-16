<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\str;

class ProductController extends Controller
{
    public function store(CreateProductRequest $request)
    {
        if($request->user()->role=="store_manager")
        {
            $image_url=str::random(32).".".$request->image_url->getClientOriginalExtension();
            Product::create([
                "store_id"=>$request->user()->store->id,
                "category_id"=>$request->category_id,
                "name"=>$request->name,
                "description"=>$request->description,
                "price"=>$request->price,
                "stock_quantity"=>$request->stock_quantity,
                "image_url"=>$image_url,
                "discount_value"=>$request->discount_value,
                "discount_start"=>$request->discount_start,
                "discount_end"=>$request->discount_end,
                "delivery_period"=>$request->delivery_period
            ]);
            Storage::disk('public')->put($image_url,file_get_contents($request->image_url));
        }
    }
    public function show(Product $product)
    {
        return $product;
    }
    public function updateProduct(UpdateProductRequest $request,$id)
    {
        $product=Product::find($id);
        $store=$product->store;
        if($request->user()->role=="store_manager"&&$request->user()==$store->user)
        {
            if($request->image_url)
            {
                $image_url=str::random(32).".".$request->image_url->getClientOriginalExtension();
                Storage::disk('public')->put($image_url,file_get_contents($request->image_url));
                $product->image_url=$image_url;
            }
            if($request->store_id)
             $product->store_id=$request->stor_id;
            if($request->category_id)
             $product->category_id=$request->category_id;
            if($request->name)
                $product->name=$request->name;
            if($request->description)
             $product->description=$request->description;
            if($request->price)
             $product->price=$request->price;
            if($request->stock_quantity)
                $product->stock_quantity=$request->stock_quantity;
            if($request->duscount_value)
                $product->duscount_value=$request->duscount_value;
            if($request->duscount_start)
                $product->duscount_start=$request->duscount_start;
            if($request->duscount_end)
                $product->duscount_end=$request->duscount_end;
            if($request->delivery_period)
                $product->delivery_period=$request->delivery_period;
            $product->save();
        }
    }
    public function destroy(Request $request,Product $product)
    {
        $store=$product->store;
        if($request->user()->role=="store_manager"&&$request->user()==$store->user)
        {
            $product->delete();
        }
    }
    public function category(string $name)
    {
        $category=Category::where('name',$name)->first();
        return $category->products;
    }
    public function offer()
    {
        $offers=Product::where('discount_value','!=',null)->where('discount_start','<=',now())->where('discount_end','>',now())->latest()->take(3)->get();
        return $offers;
    }
    public function priceRange($startRange,$endRange)
    {
        $products=Product::where('price','>=',$startRange)->where('price','<=',$endRange)->get();
        return $products;
    }
}
