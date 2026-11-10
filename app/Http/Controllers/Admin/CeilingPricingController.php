<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PricingCategory;
use App\Http\Controllers\Controller;
use App\Models\PricingVariable;
use Illuminate\View\View;

/**
 * Ceník vypínaných stropů: fólie, lišty, LED a příslušenství.
 * Ukládání a přidávání řádků obsluhuje PricingVariableController.
 */
class CeilingPricingController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.ceiling', [
            'variables' => PricingVariable::query()
                ->where('category', PricingCategory::Ceiling)
                ->orderByDesc('is_system')
                ->orderBy('id')
                ->get(),
        ]);
    }
}
