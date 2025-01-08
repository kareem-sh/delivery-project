<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ArProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\str;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function store(CreateProductRequest $request)
    {
        $this->authorize('create', User::class);
        $data = $request->validated();
        $image_url = str::random(32) . "." . $request->image_url->getClientOriginalExtension();
        Storage::disk('public')->put($image_url, file_get_contents($request->image_url));
        $data->image_url = $image_url;

        Product::create([
            "store_id" => $request->user()->store->id,
            ...$data
        ]);
    }
    public function show($id)
    {
        $product = Product::find($id);
        if (Auth::user()->lang == "en") {
            return response()->json(new ProductResource($product));
        } else {
            return response()->json(new ArProductResource($product));
        }
    }
    public function updateProduct(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);
        $this->authorize('update', [User::class, $product]);
        $data = $request->validated();
        if ($request->hasFile('image_url')) {
            $image_url = str::random(32) . "." . $request->image_url->getClientOriginalExtension();
            Storage::disk('public')->put($image_url, file_get_contents($request->image_url));
            $data->image_url = $image_url;
        }
        $product->update($data);
    }
    public function destroy($id)
    {
        $product = Product::find($id);
        $this->authorize('delete', [User::class, $product]);
        $product->delete();
    }
    public function category(string $name)
    {
        if (Auth::user()->lang == "en") {
            if ($name == "All") {
                $products = Product::all();
            } else {
                $category = Category::where('name', $name)->first();
                $products = $category->products;
            }
            return ProductResource::collection(($products));
        }
        if ($name == "الكل") {
            $products = Product::all();
        } else {
            $category = Category::where('name_ar', $name)->first();
            $products = $category->products;
        }
        return ArProductResource::collection(($products));
    }
    public function offer()
    {
        $offers = Product::where('discount_value', '!=', null)->where('discount_start', '<=', now())->where('discount_end', '>', now())->latest()->take(3)->get();
        if (Auth::user()->lang == "en") {
            return ProductResource::collection(($offers));
        }
        return ArProductResource::collection(($offers));
    }
    public function priceRange($startRange, $endRange)
    {
        $products = Product::where('price', '>=', $startRange)->where('price', '<=', $endRange)->get();
        if (Auth::user()->lang == "en") {
            return ProductResource::collection(($products));
        }
        return ArProductResource::collection(($products));
    }
}
