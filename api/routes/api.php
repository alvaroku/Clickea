<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\QuotationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('products', ProductController::class);

    // Services
    Route::get('/services/catalog', [ServiceController::class, 'catalog']);
    Route::apiResource('services', ServiceController::class);
    Route::patch('/services/{service}/status', [ServiceController::class, 'toggleStatus']);

    // Service Requests
    Route::get('/service-requests', [ServiceRequestController::class, 'index']);
    Route::get('/service-requests/provider', [ServiceRequestController::class, 'indexForProvider']);
    Route::post('/service-requests', [ServiceRequestController::class, 'store']);
    Route::post('/service-requests/{requestedService}/cancel', [ServiceRequestController::class, 'cancel']);

    // Quotations
    Route::get('/service-requests/{serviceRequest}/quotations', [QuotationController::class, 'index']);
    Route::put('/quotations/{quotation}', [QuotationController::class, 'update']);
    Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept']);
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject']);
    Route::post('/quotations/{quotation}/rate', [QuotationController::class, 'rate']);

    // Categories
    Route::get('/categories/all', [CategoryController::class, 'all']);
    Route::apiResource('categories', CategoryController::class);
    Route::patch('/categories/{category}/status', [CategoryController::class, 'toggleStatus']);

    // User management (Superadmin)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
