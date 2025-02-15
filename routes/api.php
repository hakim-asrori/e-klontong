<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SignController;
use App\Http\Middleware\UserAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/unauthorized', function () {
    return [
        'code' => 880,
        'messages' => 'Unauthorized'
    ];
})->name('login');

Route::post('app', function () {
    return [
        "version" => Application::VERSION
    ];
});

Route::post('products', [ProductController::class, 'index']);
Route::post('categories', [CategoryController::class, 'index']);

Route::middleware(['guest'])->prefix('sign')->group(function () {
    Route::post('up', [SignController::class, 'up']);
    Route::post('in', [SignController::class, 'in']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('carts', [CartController::class, 'index']);
    Route::post('cart/store', [CartController::class, 'store']);
    Route::post('cart/update', [CartController::class, 'update']);
    Route::post('cart/delete', [CartController::class, 'delete']);

    Route::post('orders', [OrderController::class, 'index']);
    Route::post('order/store', [OrderController::class, 'store']);

    Route::post('addresses', [AddressController::class, 'index']);
    Route::prefix('address')->group(function () {
        Route::post('default', [AddressController::class, 'default']);
        Route::post('update-default', [AddressController::class, 'updateDefault']);
        Route::post('delete', [AddressController::class, 'delete']);
        Route::post('store', [AddressController::class, 'store']);
        Route::post('update', [AddressController::class, 'update']);
        Route::post('provinces', [AddressController::class, 'getProvinces']);
        Route::post('regencies', [AddressController::class, 'getRegencies']);
        Route::post('districts', [AddressController::class, 'getDistricts']);
        Route::post('villages', [AddressController::class, 'getVillages']);
    });
});
