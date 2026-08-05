<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRibbonRequest;
use App\Models\Ribbon;
use Illuminate\Http\RedirectResponse;

class RibbonController extends Controller
{
    public function update(UpdateRibbonRequest $request, Ribbon $ribbon): RedirectResponse
    {
        $validated = $request->validated();

        $ribbon->update([
            'name' => $validated['name'],
            'price_per_meter' => $validated['price_per_meter'],
            'allowed_ratios' => $validated['allowed_ratios'],
            'is_active' => $validated['is_active'] ?? $ribbon->is_active,
        ]);

        return redirect()
            ->route('admin.pricing.index')
            ->with('status', "Stuha „{$ribbon->name}“ byla uložena.");
    }
}
