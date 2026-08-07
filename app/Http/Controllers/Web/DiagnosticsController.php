<?php

namespace App\Http\Controllers\Web;

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * DOČASNÁ diagnostika 500 na homepage — po nalezení příčiny smazat.
 */
class DiagnosticsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $results = [];

        $results['cache'] = $this->try(fn () => Cache::remember('diag-test', 60, fn () => 'ok'));
        $results['pricer_resolve'] = $this->try(fn () => app(ItemPricer::class)::class);
        $results['vat'] = $this->try(fn () => app(ItemPricer::class)->vatRate());

        foreach (ItemType::cases() as $type) {
            $results['price_'.$type->value] = $this->try(
                fn () => app(ItemPricer::class)->price($type, PublicPresets::referenceParameters($type))->unitPrice,
            );
        }

        $results['view_hero'] = $this->try(fn () => strlen(view('web.sections._hero')->render()));
        $results['view_catalog'] = $this->try(fn () => strlen(view('web.sections._catalog', ['pricesFrom' => []])->render()));
        $results['view_gallery'] = $this->try(fn () => strlen(view('web.sections._gallery')->render()));
        $results['view_about'] = $this->try(fn () => strlen(view('web.sections._about')->render()));
        $results['livewire'] = $this->try(fn () => strlen(\Livewire\Livewire::mount(\App\Livewire\Web\PriceEstimator::class)));

        return response()->json($results);
    }

    private function try(callable $probe): array
    {
        try {
            return ['ok' => true, 'value' => $probe()];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e::class.': '.$e->getMessage()];
        }
    }
}
