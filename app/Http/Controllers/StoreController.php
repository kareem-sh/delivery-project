<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny',User::class);
        $stores=Store::all();
        return $stores;
    }
    public function store(CreateStoreRequest $request)
    {
        $this->authorize('create',User::class);
        $data=$request->validated();
        if($request->hasFile('image'))
        {
            $image=str::random(32).".".$request->image->getClientOriginalExtension();
             Storage::disk('public')->put($image,file_get_contents($request->image));
        }
        $data->image=$image;
        Store::create([
            "user_id"=>$request->user()->id,
            $data
        ]);
    }
    public function show(Store $store)
    {
        $this->authorize('view',[User::class,$store]);
        return $store;
    }
    public function updateStore(UpdateStoreRequest $request,$id)
    {
        $store=Store::find($id);
        $this->authorize('update',[User::class,$store]);
        $data = $request->validated();
        if($request->hasFile('image'))
        {
            $image=str::random(32).".".$request->image->getClientOriginalExtension();
            Storage::disk('public')->put($image,file_get_contents($request->image));
        }
        $data->image=$image;
        $store->update($data);
    }
    public function destroy(Store $store)
    {
        $this->authorize('delete',[User::class,$store]);
        $store->delete();
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
