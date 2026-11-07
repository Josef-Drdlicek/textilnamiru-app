<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingVariable;
use App\Models\Ribbon;
use Illuminate\View\View;

/**
 * Ceník na jedné stránce: proměnné úkonů (včetně vlastních řádků dílny)
 * a řasící stuhy. Změny se projeví jen v nových kalkulacích — odeslané
 * zakázky mají ceny zamrazené.
 */
class PricingController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.index', [
            'variables' => PricingVariable::query()->orderByDesc('is_system')->orderBy('id')->get(),
            'ribbons' => Ribbon::query()->orderBy('sort_order')->get(),
        ]);
    }
}
