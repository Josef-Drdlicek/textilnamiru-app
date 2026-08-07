<?php

namespace App\Http\Controllers\Web;

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('web.home', [
            'pricesFrom' => $this->pricesFrom(),
        ]);
    }

    public function privacy(): View
    {
        return view('web.gdpr');
    }

    /**
     * Katalogová „cena od" pro každý typ — s DPH, zaokrouhlená na desítky
     * korun nahoru. Ceník se mění zřídka, hodinová cache bohatě stačí.
     *
     * @return array<string, int>
     */
    private function pricesFrom(): array
    {
        return Cache::remember('web.prices-from', now()->addHour(), function (): array {
            $pricer = app(ItemPricer::class);

            try {
                $vatRate = $pricer->vatRate();
            } catch (\DomainException $e) {
                report($e);

                return [];
            }

            $prices = [];

            foreach (ItemType::cases() as $type) {
                // Díra v ceníku nesmí shodit celý web — dlaždice bez ceny
                // spadne na chip „Na poptávku".
                try {
                    $price = $pricer->price($type, PublicPresets::referenceParameters($type));
                    $prices[$type->value] = (int) (ceil($price->unitPriceWithVat($vatRate) / 10) * 10);
                } catch (\DomainException $e) {
                    report($e);
                }
            }

            return $prices;
        });
    }
}
