<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories=Category::all();
        return $categories;
    }
    public function store(CreateCategoryRequest $request)
    {
        if($request->user()->role=="admin")
        {
            Category::create($request->all());
        }
    }
    public function destroy(Request $request,Category $category)
    {
        if($request->user()->role=="admin")
        {
            $category->delete();

        }
    }
}
