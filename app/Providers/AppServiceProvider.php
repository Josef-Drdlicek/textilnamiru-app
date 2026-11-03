<?php

namespace App\Providers;

use App\Domain\Pricing\PriceList;
use App\Models\Inquiry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
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
        // Po úpravě CSS/JS ať prohlížeč nenačte starou cache (hlavně Chrome).
        Vite::createAssetPathsUsing(function (string $path, ?bool $secure = null): string {
            $url = asset($path, $secure);

            return $url.(str_contains($url, '?') ? '&' : '?').'v=3';
        });

        // Ochrana veřejného poptávkového formuláře před spamem.
        RateLimiter::for('inquiries', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(20)->by($request->ip()),
        ]);

        // Počet nových poptávek pro odznak v admin navigaci.
        View::composer('components.layouts.app', function (\Illuminate\View\View $view): void {
            $user = auth()->user();

            $view->with(
                'newInquiriesCount',
                $user?->isAdmin() ? Inquiry::query()->new()->count() : 0,
            );
        });
    }
}
