<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AddCustomPricingVariable;
use App\Enums\PricingCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePricingVariableRequest;
use App\Models\PricingVariable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingVariableController extends Controller
{
    /**
     * Hromadná úprava hodnot ceníkových proměnných.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'values' => ['required', 'array'],
            'values.*' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'category' => ['nullable', Rule::enum(PricingCategory::class)],
        ]);

        $category = $request->enum('category', PricingCategory::class) ?? PricingCategory::Sewing;

        $variables = PricingVariable::query()
            ->whereIn('code', array_keys($validated['values']))
            ->get();

        foreach ($variables as $variable) {
            $variable->update(['value' => $validated['values'][$variable->code]]);
        }

        return redirect()
            ->route($category->adminRoute())
            ->with('status', 'Ceník byl uložen. Změny platí pro nové kalkulace, odeslané zakázky zůstávají beze změny.');
    }

    public function store(StorePricingVariableRequest $request, AddCustomPricingVariable $addVariable): RedirectResponse
    {
        $variable = $addVariable->handle(
            name: $request->string('name')->trim()->toString(),
            unit: $request->filled('unit') ? $request->string('unit')->trim()->toString() : null,
            value: (float) $request->input('value'),
            category: $request->category(),
        );

        return redirect()
            ->route($variable->category->adminRoute())
            ->with('status', "Položka „{$variable->name}“ byla přidána do ceníku.");
    }

    /**
     * Mazat jde jen vlastní řádky — systémové položky používají vzorce.
     */
    public function destroy(PricingVariable $variable): RedirectResponse
    {
        abort_if($variable->is_system, 403, 'Systémovou položku ceníku nelze smazat.');

        $variable->delete();

        return redirect()
            ->route($variable->category->adminRoute())
            ->with('status', "Položka „{$variable->name}“ byla smazána.");
    }
}
