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
use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TestNotification;
use App\Services\FcmService;

Route::prefix('/auth')->group(function () {
    //Take the fcm token and check the need for the user phone number

    //Make lgin or send an OTP
    Route::post('/handlePhoneNumber', [AuthController::class, 'handlePhoneNumber'])->name('login');

    // Verify the OTP
    Route::post('/verify', [AuthController::class, 'verify']);

    // Resend OTP
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/favorites', [UserController::class, 'favorites']);
    Route::post('users/toggle_favorites', [UserController::class, 'toggle_favorites']);
    Route::get('users/orders', [UserController::class, 'getUserOrders']);
    Route::get('users/notifications', [UserController::class, 'getUserNotifications']);
    Route::apiResource('/users', UserController::class);
});
Route::middleware(['auth:sanctum'])->group(function () {

    // add a single product to cart
    Route::post('/cart/add', [OrderController::class, 'addToCart']);

    // remove a single product from the cart
    Route::delete('/cart/remove', [OrderController::class, 'removeFromCart']);

    // Show an order with its sub-orders and items
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // return all orders
    Route::get('/orders', [OrderController::class, 'index']);

    // update the cart quantities
    Route::put('/cart/update', [OrderController::class, 'updateCart']);

    // Update the status of an pending order
    Route::put('/orders/{id}', [OrderController::class, 'updatePendingOrder']);


    // Cancel the entire order and all its sub-orders
    Route::delete('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Change the order status from cart to pending
    Route::patch('/cart/submit', [OrderController::class, 'submitCart']);
});

// Get the cart orders for a specific user
Route::get('/cart', [OrderController::class, 'getCart'])->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');

Route::controller(StoreController::class)->group(function () {
    Route::get('stores/{id}/categories/{name}', 'ProductsAsCategory')->middleware('auth:sanctum');
    Route::get('search/{prefix}', 'search')->middleware('auth:sanctum');
    Route::get('stores/{id}/categories', 'CategoryOfStore')->middleware('auth:sanctum');
    Route::post('stores/update/{id}', 'updateStore')->middleware('auth:sanctum');
})->middleware('auth:sanctum');
Route::apiResource('stores', StoreController::class)->middleware('auth:sanctum');

Route::controller(ProductController::class)->group(function () {
    Route::post('products/update/{id}', 'updateProduct');
    Route::get('products/category/{name}', 'category')->middleware('auth:sanctum');
    Route::get('products.offer', 'offer')->middleware('auth:sanctum');
    Route::get('products/range/{startRange}/{endRange}', 'priceRange')->middleware('auth:sanctum');
})->middleware('auth:sanctum');
Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');

Route::get('test', function () {
    $order = Order::find(1);
    $user = $order->user;
    $user->notify(new OrderStatusChanged($order, 'canceled'));

    // Trigger Firebase notification manually after calling notify()
    $notification = new OrderStatusChanged($order, 'canceled');
    $notification->toFirebase($user); // Manually call toFirebase
});
