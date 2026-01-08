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
use App\Http\Controllers\Api\TestMailController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/test-mail', [TestMailController::class, 'sendTestMail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/picture', [ProfileController::class, 'uploadPicture']);
    Route::delete('/profile/picture', [ProfileController::class, 'deletePicture']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/read/all', [NotificationController::class, 'deleteAllRead']);

    Route::apiResource('products', ProductController::class);

    // Services
    Route::get('/services/catalog', [ServiceController::class, 'catalog']);
    Route::apiResource('services', ServiceController::class);
    Route::patch('/services/{service}/status', [ServiceController::class, 'toggleStatus']);
    Route::delete('/services/{service}/images/{file}', [ServiceController::class, 'deleteImage']);

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
