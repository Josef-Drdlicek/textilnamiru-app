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
Route::get('/kalkulacka', [Web\PageController::class, 'kalkulacka'])->name('web.kalkulacka');
Route::get('/kalkulacka/zakazkove-sivani', [Web\PageController::class, 'kalkulackaSivani'])->name('web.kalkulacka.sivani');
Route::get('/kalkulacka/vypinane-stropy', [Web\PageController::class, 'kalkulackaStropy'])->name('web.kalkulacka.stropy');
Route::permanentRedirect('/kalkulacka/stinici-technika', '/poptavka?produkt=shades');
Route::get('/realizace', [Web\PageController::class, 'realizace'])->name('web.realizace');
Route::get('/zaclony-a-zavesy', [Web\PageController::class, 'zaclonyAZavesy'])->name('web.zaclony-a-zavesy');
// Dřívější katalog sortimentu: staré odkazy a vyhledávače přesměrovat, ne 404.
Route::permanentRedirect('/sortiment', '/zaclony-a-zavesy');
Route::get('/zaclony-a-zavesy/zaclony', [Web\PageController::class, 'zaclony'])->name('web.zaclony');
Route::get('/textil-foto/{product}', Web\TextileImageController::class)->name('web.textile-image');
Route::get('/textil-foto/barva/{color}', [Web\TextileImageController::class, 'color'])->name('web.textile-color-image');
Route::get('/zaclony-a-zavesy/zaclony/{product:slug}', [Web\PageController::class, 'zaclona'])->name('web.zaclony.show');
Route::get('/zaclony-a-zavesy/zavesy', [Web\PageController::class, 'zavesy'])->name('web.zavesy');
Route::get('/zaclony-a-zavesy/zavesy/{product:slug}', [Web\PageController::class, 'zaves'])->name('web.zavesy.show');
Route::get('/zaveseni-textilu', [Web\PageController::class, 'zaveseniTextilu'])->name('web.zaveseni-textilu');
Route::get('/zaveseni-textilu/{product}', [Web\PageController::class, 'zaveseniProdukt'])->name('web.zaveseni.show');
Route::get('/zaveseni-foto/{image}', Web\RailImageController::class)->name('web.rail-image');
Route::get('/vypinany-podhled', [Web\PageController::class, 'vypinanyPodhled'])->name('web.vypinany-podhled');
Route::get('/stinici-technika', [Web\PageController::class, 'stiniciTechnika'])->name('web.stinici-technika');
Route::get('/stinici-technika/rolety', [Web\PageController::class, 'rolety'])->name('web.rolety');
Route::get('/stinici-technika/rolety/{product:slug}', [Web\PageController::class, 'roleta'])->name('web.rolety.show');
Route::get('/roleta-foto/{image}', Web\ShadeImageController::class)->name('web.shade-image');
Route::get('/roleta-latka/{fabric}', Web\ShadeFabricImageController::class)->name('web.shade-fabric-image');
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
    Route::get('/quotes', [OrderController::class, 'quotes'])->name('orders.quotes');
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
        Route::get('/orders/{order}/sheet', Admin\OrderSheetController::class)->name('orders.sheet');
        Route::post('/orders/{order}/status', [Admin\OrderStatusController::class, 'store'])->name('orders.status');
        Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        // Ceník
        Route::get('/pricing', [Admin\PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing/variables', [Admin\PricingVariableController::class, 'update'])->name('pricing.variables.update');
        Route::post('/pricing/variables', [Admin\PricingVariableController::class, 'store'])->name('pricing.variables.store');
        Route::delete('/pricing/variables/{variable}', [Admin\PricingVariableController::class, 'destroy'])->name('pricing.variables.destroy');
        Route::put('/pricing/ribbons/{ribbon}', [Admin\RibbonController::class, 'update'])->name('pricing.ribbons.update');

        // Ceník textilu: látky s cenou za metr
        Route::get('/textile-pricing', [Admin\TextilePricingController::class, 'index'])->name('textile-pricing.index');
        Route::get('/textile-pricing/create', [Admin\TextilePricingController::class, 'create'])->name('textile-pricing.create');
        Route::post('/textile-pricing', [Admin\TextilePricingController::class, 'store'])->name('textile-pricing.store');
        Route::get('/textile-pricing/{product}/edit', [Admin\TextilePricingController::class, 'edit'])->name('textile-pricing.edit');
        Route::put('/textile-pricing/{product}', [Admin\TextilePricingController::class, 'update'])->name('textile-pricing.update');

        // Ceník kolejnic: kolejnice a dekorační garnýže, cena podle délky (pásma po 50 cm do 6 m)
        Route::get('/rail-pricing', [Admin\RailPricingController::class, 'index'])->name('rail-pricing.index');
        Route::put('/rail-pricing/{product}', [Admin\RailPricingController::class, 'update'])->name('rail-pricing.update');
        Route::patch('/rail-pricing/{product}/toggle', [Admin\RailPricingController::class, 'toggle'])->name('rail-pricing.toggle');

        Route::get('/ceiling-pricing', [Admin\CeilingPricingController::class, 'index'])->name('ceiling-pricing.index');

        // Ceník rolet: cena podle rozměru (tabulka šířka × výška u každé varianty)
        Route::get('/shade-pricing', [Admin\ShadePricingController::class, 'index'])->name('shade-pricing.index');
        Route::get('/shade-pricing/{variant}', [Admin\ShadePricingController::class, 'edit'])->name('shade-pricing.edit');
        Route::put('/shade-pricing/{variant}/prices', [Admin\ShadePricingController::class, 'updatePrices'])->name('shade-pricing.prices');
        Route::put('/shade-pricing/{variant}/settings', [Admin\ShadePricingController::class, 'updateSettings'])->name('shade-pricing.settings');
        Route::patch('/shade-pricing/products/{product}/toggle', [Admin\ShadePricingController::class, 'toggle'])->name('shade-pricing.toggle');
        Route::redirect('/blind-pricing', '/admin/shade-pricing')->name('blind-pricing.index');

        // Textil pro rolety: látky s cenovou skupinou A–D
        Route::get('/shade-fabrics', [Admin\ShadeFabricController::class, 'index'])->name('shade-fabrics.index');
        Route::patch('/shade-fabrics/{fabric}/toggle', [Admin\ShadeFabricController::class, 'toggle'])->name('shade-fabrics.toggle');

        // Uživatelé
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
    });
});
