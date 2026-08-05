<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConfiguratorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderItemSchemaController;
use App\Http\Controllers\OrderSubmissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', HomeController::class)->name('home');

    // Zakázky designéra
    Route::get('/orders', [OrderController::class, 'welcome'])->name('orders.welcome');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Konfigurátory položek
    Route::get('/orders/{order}/items/{type}/create', [ConfiguratorController::class, 'create'])->name('items.create');
    Route::get('/orders/{order}/items/{item}/edit', [ConfiguratorController::class, 'edit'])->scopeBindings()->name('items.edit');
    Route::delete('/items/{item}', [OrderItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/items/{item}/schema.svg', OrderItemSchemaController::class)->name('items.schema');

    // Finalizace objednávky
    Route::get('/orders/{order}/materials', [OrderSubmissionController::class, 'create'])->name('orders.materials');
    Route::post('/orders/{order}/submit', [OrderSubmissionController::class, 'store'])->name('orders.submit');

    // Administrace (dílna)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [Admin\OrderStatusController::class, 'store'])->name('orders.status');
        Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        // Ceník
        Route::get('/pricing', [Admin\PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing/variables', [Admin\PricingVariableController::class, 'update'])->name('pricing.variables.update');
        Route::put('/pricing/ribbons/{ribbon}', [Admin\RibbonController::class, 'update'])->name('pricing.ribbons.update');
        Route::put('/pricing/blind-prices', [Admin\BlindPriceController::class, 'update'])->name('pricing.blind-prices.update');

        // Uživatelé
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
