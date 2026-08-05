<?php

namespace App\Providers;

use App\Domain\Pricing\PriceList;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ceník se načte z DB nejvýše jednou za request; kalkulátory pak
        // počítají čistě v paměti nad konzistentním snapshotem.
        $this->app->scoped(PriceList::class, fn () => PriceList::fromDatabase());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
