<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConfiguratorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDeferralController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderItemSchemaController;
use App\Http\Controllers\OrderSubmissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web;
use Illuminate\Support\Facades\Route;

// Veřejný web — marketing, kalkulačka a poptávky (bez přihlášení)
Route::get('/', [Web\PageController::class, 'home'])->name('web.home');
Route::get('/sortiment', [Web\PageController::class, 'sortiment'])->name('web.sortiment');
Route::get('/kalkulacka', [Web\PageController::class, 'kalkulacka'])->name('web.kalkulacka');
Route::get('/kalkulacka/zakazkove-sivani', [Web\PageController::class, 'kalkulackaSivani'])->name('web.kalkulacka.sivani');
Route::get('/kalkulacka/vypinane-stropy', [Web\PageController::class, 'kalkulackaStropy'])->name('web.kalkulacka.stropy');
Route::get('/kalkulacka/stinici-technika', [Web\PageController::class, 'kalkulackaStinici'])->name('web.kalkulacka.stinici');
Route::get('/realizace', [Web\PageController::class, 'realizace'])->name('web.realizace');
Route::get('/vypinany-podhled', [Web\PageController::class, 'vypinanyPodhled'])->name('web.vypinany-podhled');
Route::get('/stinici-technika', [Web\PageController::class, 'stiniciTechnika'])->name('web.stinici-technika');
Route::get('/ochrana-osobnich-udaju', [Web\PageController::class, 'privacy'])->name('web.privacy');
Route::get('/poptavka', [Web\InquiryController::class, 'create'])->name('web.inquiry.create');
Route::post('/poptavka', [Web\InquiryController::class, 'store'])
    ->middleware('throttle:inquiries')
    ->name('web.inquiry.store');
Route::get('/poptavka/dekujeme', [Web\InquiryController::class, 'thanks'])->name('web.inquiry.thanks');
Route::get('/sitemap.xml', Web\SitemapController::class)->name('web.sitemap');

Route::middleware('guest')->group(function () {
    // Přihlášení interiérových studií a dílny; /login zůstává kvůli starým odkazům.
    Route::get('/b2b', [LoginController::class, 'create'])->name('login');
    Route::post('/b2b', [LoginController::class, 'store'])->name('login.store');
    Route::permanentRedirect('/login', '/b2b');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/prehled', HomeController::class)->name('home');

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
    Route::post('/orders/{order}/defer', [OrderDeferralController::class, 'store'])->name('orders.defer');

    // Administrace (dílna)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Poptávky z veřejného webu
        Route::get('/inquiries', [Admin\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [Admin\InquiryController::class, 'show'])->name('inquiries.show');
        Route::post('/inquiries/{inquiry}/status', [Admin\InquiryStatusController::class, 'store'])->name('inquiries.status');
        Route::post('/inquiries/{inquiry}/convert', [Admin\InquiryConversionController::class, 'store'])->name('inquiries.convert');
        Route::delete('/inquiries/{inquiry}', [Admin\InquiryController::class, 'destroy'])->name('inquiries.destroy');

        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [Admin\OrderStatusController::class, 'store'])->name('orders.status');
        Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        // Ceník
        Route::get('/pricing', [Admin\PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing/variables', [Admin\PricingVariableController::class, 'update'])->name('pricing.variables.update');
        Route::post('/pricing/variables', [Admin\PricingVariableController::class, 'store'])->name('pricing.variables.store');
        Route::delete('/pricing/variables/{variable}', [Admin\PricingVariableController::class, 'destroy'])->name('pricing.variables.destroy');
        Route::put('/pricing/ribbons/{ribbon}', [Admin\RibbonController::class, 'update'])->name('pricing.ribbons.update');

        // Uživatelé
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
