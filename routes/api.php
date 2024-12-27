<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;


Route::prefix('/auth')->group(function () {
    //Take the fcm token and check the need for the user phone number

    //Make lgin or send an OTP
    Route::post('/handlePhoneNumber', [AuthController::class, 'handlePhoneNumber'])->name('login');
   
   // Verify the OTP
    Route::post('/verify', [AuthController::class, 'verify']);
    
    // Resend OTP
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
});
    
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/favorites', [UserController::class, 'favorites']);
    Route::post('users/toggle_favorites', [UserController::class, 'toggle_favorites']);
    Route::get('users/orders',[UserController::class,'getUserOrders']);
    Route::apiResource('/users', UserController::class);



    Route::apiResource('stores', StoreController::class);
    Route::controller(StoreController::class)->group(function () {
        Route::get('stores/{id}/products/{name}', 'ProductsAsCategory');
        Route::post('stores/update/{id}', 'updateStore');
        Route::get('search/{prefix}', 'search');
    })->middleware('auth:sanctum');

    Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');

    Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');
    Route::controller(ProductController::class)->group(function () {
        Route::get('products/category/{name}', 'category');
        Route::get('products/offer', 'offer');
        Route::get('products/range/{startRange}/{endRange}', 'priceRange');
        Route::post('products/update/{id}', 'updateProduct');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {

    // Create a new order
    Route::post('/orders', [OrderController::class, 'store']);

    // Show an order with its sub-orders and items
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // Show an order with its items only 
    // Route::get('/orders/{id}/items', [OrderController::class, 'getOrderItems']);

    // Update the status of an order
    Route::put('/orders/{id}', [OrderController::class, 'update']);

    // Cancel the entire order and all its sub-orders
    Route::delete('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

     // Cancel a sub-order
    // Route::delete('/sub-orders/{id}/cancel', [OrderController::class, 'cancelSubOrder']);

    // Change the order status from cart to pending
    Route::put('/orders/{id}/submit', [OrderController::class, 'submit']);

   
});

     // Get the cart orders for a specific user
     Route::get('/cart', [OrderController::class, 'getCart'])->middleware('auth:sanctum');
     