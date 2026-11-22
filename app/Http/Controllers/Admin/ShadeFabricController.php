<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ShadeFabricCollection;
use App\Enums\ShadePriceCategory;
use App\Http\Controllers\Controller;
use App\Models\ShadeFabric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Textil pro rolety: přehled látek s fotkou, vlastnostmi a cenovou skupinou A–D.
 */
class ShadeFabricController extends Controller
{
    public function index(Request $request): View
    {
        $collection = ShadeFabricCollection::tryFrom((string) $request->query('kolekce'));
        $category = ShadePriceCategory::tryFrom((string) $request->query('skupina'));
        $search = trim((string) $request->query('hledat'));

        $fabrics = ShadeFabric::query()
            ->when($collection, fn ($query) => $query->where('collection', $collection))
            ->when($category, fn ($query) => $query->where('price_category', $category))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(60)
            ->withQueryString();

        return view('admin.shade-fabrics.index', [
            'fabrics' => $fabrics,
            'collection' => $collection,
            'category' => $category,
            'search' => $search,
            'counts' => ShadeFabric::query()->selectRaw('price_category, count(*) as total')->groupBy('price_category')->pluck('total', 'price_category'),
        ]);
    }

    public function toggle(ShadeFabric $fabric): RedirectResponse
    {
        $fabric->update(['is_active' => ! $fabric->is_active]);

        return back()->with('status', "Látka „{$fabric->name}“ ".($fabric->is_active ? 'je teď v nabídce.' : 'je skrytá.'));
    }
}
