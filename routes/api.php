<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Auth Routes - with rate limiting
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Products - Public Read (no auth required for index/show)
    Route::post('/products', [ProductController::class, 'store']); // Seller only
    Route::put('/products/{id}', [ProductController::class, 'update']); // Seller only
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Seller only
    Route::get('/seller/products', [ProductController::class, 'sellerProducts']); // Seller only

    // Orders
    Route::post('/orders', [OrderController::class, 'store']); // Buyer only
    Route::get('/orders', [OrderController::class, 'index']); // Buyer orders
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Buyer/Seller view
    Route::get('/seller/orders', [OrderController::class, 'sellerOrders']); // Seller only
    Route::put('/seller/orders/{id}/ship', [OrderController::class, 'ship']); // Seller only
    Route::put('/orders/{id}/confirm', [OrderController::class, 'confirm']); // Buyer only
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']); // Buyer only

    // Payments
    Route::post('/orders/{id}/pay', [PaymentController::class, 'pay']); // Buyer only
});

// Public Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
