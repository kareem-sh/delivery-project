<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('/auth')->group(function () {
    Route::post('/handleRequest', [AuthController::class, 'handleRequest']);
    Route::post('/handlePhoneNumber', [AuthController::class, 'handlePhoneNumber']);
    Route::post('/verify', [AuthController::class, 'verify']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
});

Route::apiResource('/users',UserController::class)->middleware('auth:sanctum');

Route::apiResource('users',UserController::class);

Route::apiResource('stores',StoreController::class)->middleware('auth:sanctum');
Route::controller(StoreController::class)->group(function(){
    Route::get('stores/{id}/products/{name}','ProductsAsCategory');
    Route::post('stores/update/{id}','updateStore');
    Route::get('search/{prefix}','search');
})->middleware('auth:sanctum');

Route::apiResource('categories',CategoryController::class)->middleware('auth:sanctum');

Route::apiResource('products',ProductController::class)->middleware('auth:sanctum');
Route::controller(ProductController::class)->group(function(){
    Route::get('products/category/{name}','category');
    Route::get('products.offer','offer');
    Route::get('products/range/{startRange}/{endRange}','priceRange');
    Route::post('products/update/{id}','updateProduct');
})->middleware('auth:sanctum');


Route::apiResource('favorites',FavoriteController::class)->middleware('auth:sanctum');
