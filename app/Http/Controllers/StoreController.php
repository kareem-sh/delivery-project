<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoresResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class StoreController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $stores = Store::all();
        return StoresResource::collection(($stores));
    }
    public function store(CreateStoreRequest $request)
    {
        $this->authorize('create', User::class);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $image = str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($image, file_get_contents($request->image));
        }
        $data->image = $image;
        Store::create([
            "user_id" => $request->user()->id,
            $data
        ]);
    }
    public function show($id)
    {
        $store = Store::find($id);
        return response()->json(new StoresResource($store));
    }
    public function updateStore(UpdateStoreRequest $request, $id)
    {
        $store = Store::find($id);
        $this->authorize('update', [User::class, $store]);
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $image = str::random(32) . "." . $request->image->getClientOriginalExtension();
            Storage::disk('public')->put($image, file_get_contents($request->image));
        }
        $data->image = $image;
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
        if ($name == "All") {
            $store = Store::find($id);
            $products = $store->products;
        } else {
            $category = Category::where('name', $name)->first();
            $products = Product::where('category_id', $category->id)->where('store_id', $id)->get();
        }
        return ProductResource::collection(($products));
    }
    public function search(string $sub)
    {
        $stores = Store::where('name', 'LIKE', '%' . $sub . '%')->get();
        $products = Product::where('name', 'LIKE', '%' . $sub . '%')->get();
        return response()->json([
            "stores" => StoresResource::collection(($stores)),
            "products" => ProductResource::collection(($products))
        ]);
    }
    public function categoryOfStore($id)
    {
        $stores = Store::find($id);
        $categories = collect();
        $categories->push("All");
        foreach ($stores->products as $product) {
            $category = Category::find($product->category_id);
            $categories->push($category->name);
        }
        $category = $categories->unique();
        return collect($category)->values();
    }
}
