<?php

namespace App\Http\Controllers;

use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\Product\ArProductResource;
use App\Http\Resources\Store\ArStoreResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Store\StoreResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $stores = Store::all();
        if (Auth::user()->lang == "en") {
            return StoreResource::collection(($stores));
        }
        return ArStoreResource::collection(($stores));
    }
    public function store(CreateStoreRequest $request)
    {
        // $this->authorize('create', User::class);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $image = str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($image, file_get_contents($request->image));
            $data['image'] = $image;
        }
        Store::create([
            "user_id" => $request->user()->id,
            ...$data
        ]);
    }
    public function show($id)
    {
        $store = Store::find($id);
        if (Auth::user()->lang == "en") {
            return response()->json(new StoreResource($store));
        }
        return response()->json(new ArStoreResource($store));
    }
    public function updateStore(UpdateStoreRequest $request, $id)
    {
        $store = Store::find($id);
        //$this->authorize('update', [User::class, $store]);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $image = str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($image, file_get_contents($request->image));
            $data['image'] = $image;
        }
        $store->update($data);
    }
    public function destroy($id)
    {
        $store = Store::find($id);
        $this->authorize('delete', [User::class, $store]);
        $store->delete();
    }
    public function ProductsAsCategory($id, string $name)
    {
        $store = Store::find($id);
        if (Auth::user()->lang == "en") {
            if ($name == "All") {
                $products = $store->products;
            } else {
                $category = Category::where('name', $name)->first();
                $products = Product::where('category_id', $category->id)->where('store_id', $id)->get();
            }
            return ProductResource::collection(($products));
        }
        if ($name == "الكل") {
            $products = $store->products;
        } else {
            $category = Category::where('name_ar', $name)->first();
            $products = Product::where('category_id', $category->id)->where('store_id', $id)->get();
        }
        return ArProductResource::collection(($products));
    }
    public function search(string $sub)
    {
        if (preg_match('/[A-Za-z]/', $sub)) //if sub string is writed in english
        {
            $stores = Store::where('name', 'LIKE', '%' . $sub . '%')->get();
            $products = Product::where('name', 'LIKE', '%' . $sub . '%')->get();
        } else {
            $stores = Store::where('name_ar', 'LIKE', '%' . $sub . '%')->get();
            $products = Product::where('name_ar', 'LIKE', '%' . $sub . '%')->get();
        }
        if (Auth::user()->lang == "en") {
            return response()->json([
                "stores" => StoreResource::collection(($stores)),
                "products" => ProductResource::collection(($products))
            ]);
        }
        return response()->json([
            "stores" => ArStoreResource::collection(($stores)),
            "products" => ArProductResource::collection(($products))
        ]);
    }
    public function categoryOfStore($id)
    {
        $stores = Store::find($id);
        $categories = collect();
        if (Auth::user()->lang == "en") {
            $categories->push("All");
            foreach ($stores->products as $product) {
                $category = Category::find($product->category_id);
                $categories->push($category->name);
            }
            $category = $categories->unique();
        } else {
            $categories->push("الكل");
            foreach ($stores->products as $product) {
                $category = Category::find($product->category_id);
                $categories->push($category->name_ar);
            }
            $category = $categories->unique();
        }
        return collect($category)->values();
    }
}
