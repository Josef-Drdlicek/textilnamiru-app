<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlindPriceBand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlindPriceController extends Controller
{
    /**
     * Hromadná úprava cenové tabulky rolet (mřížka šířka × výška).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $bands = BlindPriceBand::query()
            ->whereIn('id', array_keys($validated['prices']))
            ->get();

        foreach ($bands as $band) {
            $band->update(['price' => $validated['prices'][$band->id]]);
        }

        return redirect()
            ->route('admin.pricing.index')
            ->with('status', 'Cenová tabulka rolet byla uložena.');
    }
}
