<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;


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