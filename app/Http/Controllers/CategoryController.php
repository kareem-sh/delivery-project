<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $categories = Category::all();
        return $categories;
    }
    public function store(CreateCategoryRequest $request)
    {
        $this->authorize('create', User::class);
        $data = $request->validated();
        Category::create($data);
    }
    public function destroy($id)
    {
        $category = Category::find($id);
        $this->authorize('delete', [User::class, $category]);
        $category->delete();
    }
}
