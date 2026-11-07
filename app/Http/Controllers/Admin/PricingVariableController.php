<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AddCustomPricingVariable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePricingVariableRequest;
use App\Models\PricingVariable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        ]);

        $variables = PricingVariable::query()
            ->whereIn('code', array_keys($validated['values']))
            ->get();

        foreach ($variables as $variable) {
            $variable->update(['value' => $validated['values'][$variable->code]]);
        }

        return redirect()
            ->route('admin.pricing.index')
            ->with('status', 'Ceník byl uložen. Změny platí pro nové kalkulace, odeslané zakázky zůstávají beze změny.');
    }

    public function store(StorePricingVariableRequest $request, AddCustomPricingVariable $addVariable): RedirectResponse
    {
        $variable = $addVariable->handle(
            name: $request->string('name')->trim()->toString(),
            unit: $request->filled('unit') ? $request->string('unit')->trim()->toString() : null,
            value: (float) $request->input('value'),
        );

        return redirect()
            ->route('admin.pricing.index')
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
            ->route('admin.pricing.index')
            ->with('status', "Položka „{$variable->name}“ byla smazána.");
    }
}
