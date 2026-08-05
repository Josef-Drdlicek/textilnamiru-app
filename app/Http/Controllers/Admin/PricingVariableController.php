<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingVariable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PricingVariableController extends Controller
{
    /**
     * Hromadná úprava hodnot ceníkových proměnných (33 položek najednou).
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
}
