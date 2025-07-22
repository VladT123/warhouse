<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/{order}/resume', [OrderController::class, 'resume'])->name('orders.resume');
});

Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
