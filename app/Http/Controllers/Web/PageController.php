<?php

namespace App\Http\Controllers\Web;

use App\Domain\Pricing\ItemPricer;
use App\Domain\Pricing\PublicPresets;
use App\Enums\ItemType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('web.home');
    }

    public function sortiment(): View
    {
        return view('web.sortiment', [
            'pricesFrom' => $this->pricesFrom(),
        ]);
    }

    public function kalkulacka(Request $request): View|RedirectResponse
    {
        // Starý odkaz /kalkulacka?typ=… mířil rovnou do kalkulačky šití.
        if ($request->filled('typ')) {
            return redirect()->route('web.kalkulacka.sivani', $request->only('typ'));
        }

        return view('web.kalkulacka');
    }

    public function kalkulackaSivani(): View
    {
        return view('web.kalkulacka-sivani');
    }

    public function kalkulackaStropy(): View
    {
        return view('web.kalkulacka-stropy');
    }

    public function kalkulackaStinici(): View
    {
        return view('web.kalkulacka-stinici');
    }

    public function realizace(): View
    {
        return view('web.realizace');
    }

    public function vypinanyPodhled(): View
    {
        return view('web.vypinany-podhled');
    }

    public function stiniciTechnika(): View
    {
        return view('web.stinici-technika');
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
