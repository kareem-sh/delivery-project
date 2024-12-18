<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $stores=Store::all();
        return $stores;
    }
    public function store(CreateStoreRequest $request)
    {
        if($request->user()->role=="admin")
        {
            $image=str::random(32).".".$request->image->getClientOriginalExtension();
            Store::create([
                "user_id"=>$request->user()->id,
                "name"=>$request->name,
                "latitude"=>$request->latitude,
                "longitude"=>$request->longitude,
                "image"=>$image,
                "logo_color"=>$request->logo_color,
            ]);
            Storage::disk('public')->put($image,file_get_contents($request->image));
        }
    }
    public function show(Store $store)
    {
        return $store;
    }
    public function updateStore(UpdateStoreRequest $request,$id)
    {
        if($request->user()->role=="admin")
        {
            $store=Store::find($id);
            if($request->image)
            {
                $image=str::random(32).".".$request->image->getClientOriginalExtension();
                Storage::disk('public')->put($image,file_get_contents($request->image));
                $store->image=$image;
            }
            if($request->name)
                $store->name=$request->name;
            if($request->latitude)
                $store->latitude=$request->latitude;
            if($request->longitude)
                $store->longitude=$request->longitude;
            if($request->logo_color)
                $store->logo_color=$request->logo_color;
            $store->save();
        }
    }
    public function destroy(Request $request,Store $store)
    {
        if($request->user()->role=="admin")
        {
            $store->delete();
        }
    }
    public function ProductsAsCategory($id,string $name)
    {
        $category=Category::where('name',$name)->first();
        $products=Product::where('category_id',$category->id)->where('store_id',$id)->get();
        return $products;
    }

    public function search(string $prefix)
    {
        $stores=Store::where('name','LIKE',$prefix.'%')->get();
        $products=Product::where('name','LIKE',$prefix.'%')->get();
        return response()->json([
            "stores"=>$stores,
            "products"=>$products
        ]);
    }
}
