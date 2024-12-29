<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function store(CreateProductRequest $request)
    {
        // Authorize the action
        $this->authorize('create', Product::class);

        $data = $request->validated();

        // Handle the product image
        $image_url = Str::random(32) . "." . $request->image_url->getClientOriginalExtension();
        Storage::disk('public')->put($image_url, file_get_contents($request->image_url));
        $data['image_url'] = $image_url;

        // Create the product
        Product::create([
            "store_id" => $request->user()->store->id,
            ...$data
        ]);

        return response()->json(['message' => 'Product created successfully.'], 201);
    }

    public function show(Product $product)
    {
        // Authorize the view action
        $this->authorize('view', $product);

        return response()->json($product);
    }

    public function updateProduct(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        // Authorize the update action
        $this->authorize('update', $product);

        $data = $request->validated();

        // Handle the product image
        if ($request->hasFile('image_url')) {
            $image_url = Str::random(32) . "." . $request->image_url->getClientOriginalExtension();
            Storage::disk('public')->put($image_url, file_get_contents($request->image_url));
            $data['image_url'] = $image_url;
        }

        // Update the product
        $product->update($data);

        return response()->json(['message' => 'Product updated successfully.']);
    }

    public function destroy(Product $product)
    {
        // Authorize the delete action
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function category(string $name)
    {
        // Authorize viewing all products in a category
        $this->authorize('viewAny', Product::class);

        $category = Category::where('name', $name)->firstOrFail();
        $products = $category->products;

        return response()->json($products);
    }

    public function offer()
    {
        $offers = Product::whereNotNull('discount_value')
            ->where('discount_start', '<=', now())
            ->where('discount_end', '>', now())
            ->latest()
            ->take(3)
            ->get();

        return response()->json($offers);
    }

    public function priceRange($startRange, $endRange)
    {
        $products = Product::whereBetween('price', [$startRange, $endRange])->get();

        return response()->json($products);
    }
}
