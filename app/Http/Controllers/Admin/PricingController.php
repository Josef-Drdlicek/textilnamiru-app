<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlindPriceBand;
use App\Models\PricingVariable;
use App\Models\Ribbon;
use Illuminate\View\View;

/**
 * Ceník na jedné stránce: proměnné úkonů, řasící stuhy a cenová
 * tabulka rolet. Změny se projeví jen v nových kalkulacích — odeslané
 * zakázky mají ceny zamrazené.
 */
class PricingController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.index', [
            'variables' => PricingVariable::query()->orderBy('id')->get(),
            'ribbons' => Ribbon::query()->orderBy('sort_order')->get(),
            'bands' => BlindPriceBand::query()->orderBy('max_height')->orderBy('max_width')->get(),
            'bandWidths' => BlindPriceBand::query()->distinct()->orderBy('max_width')->pluck('max_width'),
            'bandHeights' => BlindPriceBand::query()->distinct()->orderBy('max_height')->pluck('max_height'),
        ]);
    }
}
