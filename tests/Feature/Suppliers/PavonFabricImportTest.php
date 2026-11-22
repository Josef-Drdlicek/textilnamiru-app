<?php

use App\Domain\Suppliers\Pavon\PavonFabricParser;
use App\Enums\ShadeFabricCollection;
use App\Enums\ShadePriceCategory;
use App\Models\ShadeFabric;
use App\Models\ShadeProduct;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Látka ve stejné struktuře jako katalog textilií pavon.cz (ověřeno 22. 11. 2026).
 *
 * @param  array<string, string>  $params
 */
function pavonFabric(int $id, string $title, string $image, array $params, bool $saleBadge = false): string
{
    $rows = '<tr><th class="nazev">Objednací číslo:</th><td>'.$title.'</td></tr>';
    foreach ($params as $label => $value) {
        $rows .= "<tr><th class=\"nazev\">{$label}</th><td>{$value} </td></tr>";
    }
    $rows .= '<tr><th class="nazev">Dostupnost:</th><td> do 1 dne </td></tr>';
    $badge = $saleBadge ? '<span class="label label-primary product-ico-3" title="Zboží je ve výprodeji">Výprodej</span>' : '';

    return <<<HTML
<div class="boxlist-item col-md-3 col-sm-6 col-height">
<article class="product-item text-center  elist textureItem" itemscope itemtype="http://schema.org/Product">
    <div class="hidden" id="data_{$id}">
        <div class="title">{$title}</div>
        <div class="img">{$image}</div>
        <div class="params"><table class="detail_params table table-striped"><tbody>{$rows}</tbody></table></div>
    </div>
    <div class="product-image boxlist-images image-adapt-height">
        <a class="picWrapA" data-id="{$id}"><img src="https://www.pavon.cz/{$image}" alt="{$title}" class="porovnavacimg"></a>
        <a class="tdNone" data-id="{$id}"><span class="product-icos">{$badge}</span></a>
    </div>
    <h2 class="boxlist-title format_nadpis3 title-line2"><a data-id="{$id}">{$title}</a></h2>
</article>
</div>
HTML;
}

function pavonFabricSpecs(string $group, string $width = '200 cm'): array
{
    return [
        'Použití' => 'rolety, posuvné panelové stěny',
        'Cenová skupina' => $group,
        'Světelná propustnost' => 'velmi průsvitné',
        'Barva' => 'bílá',
        'Složení' => 'polyester, polymerní vrstva',
        'Šíře látky' => $width,
        'Gramáž' => '175 g/m2',
        'Údržba' => 'čištění vlhkou houbou, čištění luxováním',
    ];
}

/**
 * @param  array<string, string>  $pages  cesta => HTML
 */
function fakePavonFabrics(array $pages): void
{
    // Stránky v kontejneru, ať je jde mezi dvěma importy vyměnit (Http::fake první falešnou odpověď nepřepíše).
    $fresh = ! app()->bound('pavon.fabric-pages');
    app()->instance('pavon.fabric-pages', $pages);

    if (! $fresh) {
        return;
    }

    Http::fake(function (Request $request) {
        $path = parse_url($request->url(), PHP_URL_PATH);
        $pages = app('pavon.fabric-pages');

        if (str_starts_with($path, '/upload/')) {
            return Http::response('IMG:'.$path);
        }

        return Http::response('<html><body><h1>Textilie</h1>'.($pages[$path] ?? '').'</body></html>');
    });
}

function standardCatalog(): string
{
    return pavonFabric(348, 'Col 100', 'upload/2068-1410028313.jpg', pavonFabricSpecs('A (nejlevnější)'))
        .pavonFabric(349, 'Col 4983', 'upload/2731-1410038113.jpg', pavonFabricSpecs('A* (levnější skupina A)'))
        .pavonFabric(350, 'Avensis 70', 'upload/1095-0907413966.jpg', pavonFabricSpecs('B'), saleBadge: true)
        .pavonFabric(351, 'Col 6799', 'upload/1098-0907152631.jpg', pavonFabricSpecs('Výprodej (cenová sk. A)'))
        .pavonFabric(352, 'Juno 2276', 'upload/1106-0907056837.jpg', pavonFabricSpecs('C', '200 cm, 240 cm'))
        .pavonFabric(353, 'Termo 0123', 'upload/1935-1410553256.jpg', pavonFabricSpecs('D', '183 cm'));
}

beforeEach(function () {
    Storage::fake();
});

it('imports Pavon blind fabrics with photo, specs and price category, skipping clearance', function () {
    fakePavonFabrics([
        '/textilie-standard' => standardCatalog(),
        '/textilie-screen' => pavonFabric(400, 'Screen Pro 1101', 'upload/3500-1206108732.jpg', pavonFabricSpecs('C', '250 cm')),
        '/textilie-magico' => pavonFabric(500, 'Caria 0300', 'upload/3014-1206031792.jpg', pavonFabricSpecs('C', '280 cm')),
        '/textilie-roma' => pavonFabric(600, 'Neapel', 'upload/1940-1410269014.jpg', pavonFabricSpecs('B', '300 cm')),
    ]);

    $this->artisan('shades:import-pavon-fabrics')
        ->expectsOutputToContain('Hotovo: 7 látek, výprodej vynechán (2).')
        ->assertSuccessful();

    expect(ShadeFabric::query()->orderBy('sort_order')->pluck('price_category', 'code')->map->value->all())->toBe([
        'Col 100' => 'A',
        'Col 4983' => 'A', // A* = levnější skupina A
        'Juno 2276' => 'C',
        'Termo 0123' => 'D',
        'Screen Pro 1101' => 'C',
        'Caria 0300' => 'C',
        'Neapel' => 'B',
    ]);

    $col = ShadeFabric::query()->where('code', 'Col 100')->sole();
    expect($col)
        ->name->toBe('Col 100')
        ->collection->toBe(ShadeFabricCollection::Standard)
        ->roll_width->toBe(200)
        ->is_active->toBeTrue()
        ->source_image_url->toBe('https://www.pavon.cz/upload/2068-1410028313.jpg')
        ->and($col->spec('Gramáž'))->toBe('175 g/m2')
        ->and($col->spec('Údržba'))->toBe('čištění vlhkou houbou, čištění luxováním')
        ->and(collect($col->specs)->pluck('label')->all())->not->toContain('Cenová skupina', 'Objednací číslo', 'Dostupnost', 'Použití')
        ->and(Storage::get($col->image_path))->toBe('IMG:/upload/2068-1410028313.jpg');

    expect(ShadeFabric::query()->where('code', 'Juno 2276')->sole()->roll_width)->toBe(240)
        ->and(ShadeFabric::query()->where('code', 'Caria 0300')->sole()->collection)->toBe(ShadeFabricCollection::Magico);
});

it('hides fabrics that went on clearance and keeps manual hiding on re-import', function () {
    $pages = [
        '/textilie-standard' => standardCatalog(),
        '/textilie-screen' => pavonFabric(400, 'Screen Pro 1101', 'upload/3500-1206108732.jpg', pavonFabricSpecs('C')),
        '/textilie-magico' => '',
        '/textilie-roma' => '',
    ];
    fakePavonFabrics($pages);
    $this->artisan('shades:import-pavon-fabrics --no-images')->assertSuccessful();
    expect(ShadeFabric::query()->whereNull('image_path')->count())->toBe(5);

    ShadeFabric::query()->where('code', 'Juno 2276')->update(['is_active' => false]);

    // Col 100 přešla do výprodeje, Screen Pro z katalogu zmizela.
    $pages['/textilie-standard'] = str_replace('<td>A (nejlevnější) </td>', '<td>Výprodej (cenová sk. A) </td>', standardCatalog());
    $pages['/textilie-screen'] = '';
    fakePavonFabrics($pages);
    $this->artisan('shades:import-pavon-fabrics --no-images')->assertSuccessful();

    expect(ShadeFabric::query()->where('is_active', true)->pluck('code')->sort()->values()->all())->toBe(['Col 4983', 'Termo 0123'])
        ->and(ShadeFabric::query()->count())->toBe(5);
});

it('sets the default fabric collections on blinds that have none', function () {
    fakePavonFabrics(['/textilie-standard' => standardCatalog()]);
    $magic = ShadeProduct::query()->create(['name' => 'Roleta Maestra Magic', 'slug' => 'roleta-maestra-magic']);
    $variant = $magic->variants()->create(['name' => 'Magic Box']);
    $custom = $magic->variants()->create(['name' => 'Magic Open', 'fabric_collections' => ['standard']]);

    $this->artisan('shades:import-pavon-fabrics --no-images')->assertSuccessful();

    expect($variant->refresh()->fabric_collections)->toBe(['magico'])
        ->and($custom->refresh()->fabric_collections)->toBe(['standard']);
});

it('reads the supplier price category in every wording', function (string $value, ?ShadePriceCategory $expected) {
    expect(ShadePriceCategory::fromSupplier($value))->toBe($expected);
})->with([
    ['A (nejlevnější)', ShadePriceCategory::A],
    ['A* (levnější skupina A)', ShadePriceCategory::A],
    ['D', ShadePriceCategory::D],
    ['Výprodej (cenová sk. A)', null],
    ['', null],
]);

it('marks clearance by the badge as well as by the price group', function () {
    $fabrics = (new PavonFabricParser)->parse(standardCatalog());

    expect(collect($fabrics)->filter->onSale->pluck('code')->all())->toBe(['Avensis 70', 'Col 6799']);
});

it('lists the blind fabrics for the admin with filters and lets them hide one', function () {
    fakePavonFabrics(['/textilie-standard' => standardCatalog()]);
    $this->artisan('shades:import-pavon-fabrics')->assertSuccessful();
    $admin = User::factory()->admin()->create();
    $juno = ShadeFabric::query()->where('code', 'Juno 2276')->sole();

    $this->actingAs($admin)
        ->get(route('admin.shade-fabrics.index'))
        ->assertOk()
        ->assertSeeInOrder(['Col 100', 'Col 4983', 'Juno 2276', 'Termo 0123'])
        ->assertSee('175 g/m2')
        ->assertSee($juno->imageUrl(), false)
        ->assertDontSee('Avensis 70');

    $this->actingAs($admin)
        ->get(route('admin.shade-fabrics.index', ['skupina' => 'C']))
        ->assertSee('Juno 2276')
        ->assertDontSee('Col 100');

    $this->actingAs($admin)
        ->get(route('admin.shade-fabrics.index', ['hledat' => 'termo']))
        ->assertSee('Termo 0123')
        ->assertDontSee('Juno 2276');

    $this->actingAs($admin)->patch(route('admin.shade-fabrics.toggle', $juno))->assertRedirect();
    expect($juno->refresh()->is_active)->toBeFalse();

    $this->get(route('web.shade-fabric-image', $juno))->assertOk();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.shade-fabrics.index'))
        ->assertForbidden();
});
