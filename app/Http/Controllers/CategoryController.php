<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $categories = collect();
        if (Auth::user()->lang == "en") {
            $categories->push("All");
            foreach (Category::all() as $category) {
                $categories->push($category->name);
            }
        } else {
            $categories->push("الكل");
            foreach (Category::all() as $category) {
                $categories->push($category->name_ar);
            }
        }
        return collect($categories)->values();
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
