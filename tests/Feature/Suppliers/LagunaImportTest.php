<?php

use App\Domain\Suppliers\Laguna\LagunaClient;
use App\Domain\Suppliers\Laguna\LagunaDetailParser;
use App\Enums\TextileCategory;
use App\Domain\Suppliers\Laguna\LagunaItem;
use App\Livewire\Web\TextileProductConfigurator;
use App\Models\TextileProduct;
use App\Models\User;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Výpis a detaily mají stejný tvar jako na webu Laguna Textil (ověřeno 22. 11. 2026).
 */
function lagunaRow(string $title, string $file, string $category = 'zaclony-1'): array
{
    $slug = $category.'/'.strtolower(str_replace(['/', ' '], '-', $title)).'-7';

    return [
        'id' => crc32($title.$file),
        'title' => $title,
        'image_url' => 'https://fw.photos/abc=/600x600/'.rawurlencode('https://www.lagunatextil.com/uploads/files/'.$file),
        'link' => 'https://www.lagunatextil.com/cz/kategorie-produktu/'.$slug,
        'is_new' => false,
    ];
}

function lagunaDetail(array $fields): string
{
    $blocks = collect($fields)->map(fn ($value, $label) => "<div class=\"col-12\"> <label>{$label}</label> <div> {$value} </div> </div>")->implode('');

    return "<html><head><meta charset=\"utf-8\"></head><body><div class=\"user-content mb-50px\"></div><div class=\"infos\"><div class=\"row\">{$blocks}</div></div></body></html>";
}

function fakeLaguna(): void
{
    $pages = [
        1 => [
            lagunaRow('11101/180/03', '11101_03.jpg'),
            lagunaRow('11101/180/01', '11101_01.jpg'),
            lagunaRow('11101/290/01', '11101_01-b.jpg'),
            lagunaRow('1080/315/01 FR', '1080_01.jpg'),
        ],
        2 => [
            lagunaRow('5857/45', '5857.jpg'),
            lagunaRow('5857/60', '5857-b.jpg'),
            lagunaRow('ALISSA/300/01 AV TCS', 'Alissa_01.jpg'),
            lagunaRow('ALISSA/300/01 AV TCS', 'Alissa_02.jpg'),
        ],
    ];

    // Dekorační látky (product_category_id 2): široká i úzká látka, stejný kód jako záclona.
    $drapePages = [
        1 => [
            lagunaRow('ADAM/300/01', 'Adam_01.jpg', 'dekoracni-latky'),
            lagunaRow('ADAM/300/02', 'Adam_02.jpg', 'dekoracni-latky'),
            lagunaRow('7545/140/01', '7545_01.jpg', 'dekoracni-latky'),
            lagunaRow('11101/180/05', '11101_05-dek.jpg', 'dekoracni-latky'),
        ],
    ];

    $details = [
        'adam' => lagunaDetail(['rozměr' => '300 cm', 'materiál' => '100% POLIÉSZTER FR', 'Special' => 'NEHOŘLAVÁ, BLACK OUT']),
        '7545' => lagunaDetail(['rozměr' => '140 cm', 'materiál' => '64%CO 36%PES', 'Special' => 'DIM OUT']),
        '11101' => lagunaDetail(['návod k použití' => '<img src="x.jpg">', 'rozměr' => '180 cm', 'materiál' => '100%POLIÉSZTER', 'JEDNOTKOVÁ HMOTNOST' => '54 g/m2', 'Olověná šňůra' => 'ANO']),
        '1080' => lagunaDetail(['rozměr' => '315 cm', 'materiál' => '56%VISZKÓZ 44%POLIÉSZTER', 'raport svislý' => '39,5 cm', 'Special' => 'NEHOŘLAVÁ, EXTRA size']),
        '5857' => lagunaDetail(['rozměr' => '45 cm', 'materiál' => '100% PES', 'Special' => 'VITRÁŽKA']),
        'alissa' => lagunaDetail(['rozměr' => '300 cm', 'materiál' => '45%PES 40%ACR 10%WO 5%LI']),
    ];

    Http::fake(function (Request $request) use ($pages, $drapePages, $details) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if (in_array($path, ['/cz/kategorie-produktu/zaclony-1', '/cz/kategorie-produktu/dekoracni-latky'], true)) {
            return Http::response('<html><head><meta name="csrf-token" content="token123"></head></html>');
        }
        if ($path === '/cz/filter-url') {
            $page = $request->data()['page'];
            $catalog = $request->data()['product_category_id'] === '2' ? $drapePages : $pages;

            return Http::response(['data' => $catalog[$page] ?? [], 'meta' => ['current_page' => $page, 'last_page' => count($catalog), 'total' => 8]]);
        }
        if (str_starts_with($path, '/uploads/')) {
            return Http::response(fakeJpeg($path));
        }
        foreach ($details as $prefix => $html) {
            if (str_starts_with(basename($path), $prefix)) {
                return Http::response($html);
            }
        }

        return Http::response('nenalezeno', 404);
    });
}

beforeEach(function () {
    Storage::fake();
    fakeLaguna();
});

it('imports curtains grouped by name and height with colors, photos and Czech specs', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();

    $p180 = TextileProduct::query()->where('supplier_code', '11101/180')->sole();
    expect($p180)
        ->name->toBe('Záclona 11101 – 180 cm')
        ->roll_height->toBe(180)
        ->material->toBe('100 % polyester')
        ->price_per_meter->toBeNull()
        ->is_active->toBeFalse()
        ->and($p180->specs)->toContain(['Gramáž', '54 g/m²'], ['Olověná šňůra', 'ano, už z výroby'])
        ->and($p180->colors->pluck('name')->all())->toBe(['01', '03']);

    Storage::assertExists($p180->colors->first()->image_path);
    expect(Storage::get($p180->colors->first()->image_path))->toBe(fakeJpeg('/uploads/files/11101_01.jpg'));

    // Jiná výška = samostatný produkt.
    expect(TextileProduct::query()->where('supplier_code', '11101/290')->sole()->colors)->toHaveCount(1);

    // Přípona patří k názvu, materiál a vlastnosti česky.
    $fr = TextileProduct::query()->where('supplier_code', '1080 FR/315')->sole();
    expect($fr->name)->toBe('Záclona 1080 FR – 315 cm')
        ->and($fr->material)->toBe('56 % viskóza, 44 % polyester')
        ->and($fr->specs)->toContain(['Raport svislý', '39,5 cm'], ['Vlastnosti', 'nehořlavá, extra šíře']);

    // Bez barvy: poslední číslo je výška → každá výška samostatně.
    expect(TextileProduct::query()->where('supplier_code', '5857/45')->sole()->colors->pluck('name')->all())->toBe(['Základní'])
        ->and(TextileProduct::query()->where('supplier_code', '5857/60')->exists())->toBeTrue();

    // Duplicitní kód barvy u dodavatele se nepřepíše.
    expect(TextileProduct::query()->where('supplier_code', 'ALISSA AV TCS/300')->sole()->colors->pluck('name')->all())->toBe(['01', '01 (2)']);

    expect(TextileProduct::query()->where('supplier', 'laguna')->count())->toBe(6);
});

it('can run again without duplicates and keeps prices and visibility set by the workshop', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    TextileProduct::query()->where('supplier_code', '11101/180')->update(['price_per_meter' => 199, 'is_active' => true]);

    $this->artisan('textile:import-laguna')->assertSuccessful();

    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();
    expect(TextileProduct::query()->where('supplier', 'laguna')->count())->toBe(6)
        ->and($product->price_per_meter)->toBe(199.0)
        ->and($product->is_active)->toBeTrue()
        ->and($product->colors)->toHaveCount(2);
});

it('skips photos with --no-images', function () {
    $this->artisan('textile:import-laguna --no-images')->assertSuccessful();

    expect(TextileProduct::query()->where('supplier_code', '11101/180')->sole()->colors->pluck('image_path')->filter())->toBeEmpty();
});

it('lets only the admin preview a hidden imported product', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();

    $this->get(route('web.zaclony.show', $product))->assertNotFound();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('web.zaclony.show', $product))
        ->assertOk()
        ->assertSee('Záclona 11101 – 180 cm')
        ->assertSee('Cena na poptávku')
        ->assertSee('data-textile-color="03"', false)
        ->assertSee($product->colors->firstWhere('name', '03')->imageUrl(), false)
        ->assertSee('54 g/m²');
});

it('switches the photo when the customer picks a color', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();
    $color = $product->colors->firstWhere('name', '03');

    Livewire::test(TextileProductConfigurator::class, ['product' => $product])
        ->set('color', '03')
        ->assertDispatched('textile-color-changed', name: '03', image: $color->imageUrl())
        ->assertSee('Barva 03');

    Livewire::test(TextileProductConfigurator::class, ['product' => $product])
        ->dispatch('textile-color-picked', name: '01')
        ->assertSet('color', '01')
        ->dispatch('textile-color-picked', name: 'neexistuje')
        ->assertSet('color', '01');

    $this->get(route('web.textile-color-image', $color))->assertOk();
});

it('sends an unpriced product to the inquiry without a price', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();

    Livewire::test(TextileProductConfigurator::class, ['product' => $product])
        ->set('makeType', 'tape')
        ->set('color', '03')
        ->set('hem', 'fold')
        ->set('split', 'one')
        ->set('width', 150)
        ->set('height', 160)
        ->set('tape', 'siroky70|2')
        ->assertSet('totalPriceVat', null)
        ->assertSee('Cenu této látky doplníme')
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    expect(session('inquiry.configuration'))
        ->not->toHaveKey('total_price_vat')
        ->and(session('inquiry.configuration.summary'))->toContain('Barva 03');
});

it('sends the rotated XSRF cookie on the next catalog page', function () {
    // beforeEach už má vlastní fake, který by jinak odpověděl první.
    Http::swap(new Factory);
    Http::fake(function (Request $request) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if ($path === '/cz/kategorie-produktu/zaclony-1') {
            return Http::response(
                '<html><head><meta name="csrf-token" content="meta-token"></head></html>',
                200,
                ['Set-Cookie' => 'XSRF-TOKEN=from-get; Path=/'],
            );
        }

        if ($path === '/cz/filter-url') {
            $page = $request->data()['page'];

            return Http::response(
                ['data' => [], 'meta' => ['current_page' => $page, 'last_page' => 2]],
                200,
                ['Set-Cookie' => 'XSRF-TOKEN='.($page === 1 ? 'rotated%3Dtoken' : 'later').'; Path=/'],
            );
        }

        return Http::response('nenalezeno', 404);
    });

    app(LagunaClient::class)->items(2);

    $posts = Http::recorded(fn (Request $request) => str_contains($request->url(), '/cz/filter-url'))->values();

    expect($posts)->toHaveCount(2)
        ->and($posts[0][0]->header('X-XSRF-TOKEN'))->toBe(['from-get'])
        ->and($posts[1][0]->header('X-XSRF-TOKEN'))->toBe(['rotated=token'])
        ->and($posts[1][0]->header('X-CSRF-TOKEN'))->toBe([]);
});

it('reads name, height, color and suffix from the supplier code', function (string $title, string $group, string $last, ?string $suffix) {
    $item = new LagunaItem($title, 'x.jpg', 'https://example.test');

    expect($item->groupKey())->toBe($group)
        ->and($item->lastSegment())->toBe($last)
        ->and($item->suffix())->toBe($suffix);
})->with([
    ['11101/180/03', '11101/180', '03', null],
    ['1080/315/01 FR', '1080/315 FR', '01', 'FR'],
    ['ALISSA/300/01 AV TCS', 'ALISSA/300 AV TCS', '01', 'AV TCS'],
    ['LA PEDRERA/280/01', 'LA PEDRERA/280', '01', null],
    ['PAVIA/280/04 ó.n.', 'PAVIA/280', '04', null],
    ['v/597/120', 'V/597', '120', null],
    ['H1/1368/305/01', 'H1/1368/305', '01', null],
]);

it('translates supplier materials to Czech', function (string $raw, string $expected) {
    expect((new LagunaDetailParser)->material($raw))->toBe($expected);
})->with([
    ['100%POLIÉSZTER', '100 % polyester'],
    ['100 % POIÉSZTER', '100 % polyester'],
    ['54% PES, 36% RE-PES, 9% Linen, 1% Lurex', '54 % polyester, 36 % recyklovaný polyester, 9 % len, 1 % lurex'],
    ['45%PES 40%ACR 10%WO 5%LI', '45 % polyester, 40 % akryl, 10 % vlna, 5 % len'],
    ['52% PES, 43%Pamut, 5%Viszkóz', '52 % polyester, 43 % bavlna, 5 % viskóza'],
    ['100% PES (60% RE-PES 40% PES)', '100 % polyester'],
    ['76% BAMBOO, 24% PES', '76 % bambus, 24 % polyester'],
]);

it('imports decorative fabrics as drapes, separate from curtains with the same code', function () {
    $this->artisan('textile:import-laguna zavesy')->assertSuccessful();

    $adam = TextileProduct::query()->where('supplier_code', 'ADAM/300')->sole();
    expect($adam)
        ->category->toBe(TextileCategory::Drape)
        ->name->toBe('Závěs ADAM – 300 cm')
        ->material->toBe('100 % polyester (nehořlavý)')
        ->and($adam->specs)->toContain(['Vlastnosti', 'nehořlavá, zatemňující (blackout)'])
        ->and($adam->colors->pluck('name')->all())->toBe(['01', '02']);

    expect(TextileProduct::query()->where('supplier_code', '7545/140')->sole()->roll_height)->toBe(140);

    // Stejný kód jako záclona = jiný produkt v jiné kategorii.
    $this->artisan('textile:import-laguna zaclony')->assertSuccessful();
    expect(TextileProduct::query()->where('supplier_code', '11101/180')->pluck('category')->map->value->sort()->values()->all())
        ->toBe(['curtain', 'drape']);
});

it('imports both catalogs with vse and rejects an unknown one', function () {
    $this->artisan('textile:import-laguna vse --no-images')->assertSuccessful();

    expect(TextileProduct::query()->where('category', TextileCategory::Drape)->count())->toBe(3)
        ->and(TextileProduct::query()->where('category', TextileCategory::Curtain)->where('supplier', 'laguna')->count())->toBe(6);

    $this->artisan('textile:import-laguna rolety')->assertExitCode(2);
});

it('shows drapes on their own page and prices them as drapes', function () {
    $this->artisan('textile:import-laguna zavesy')->assertSuccessful();
    $narrow = TextileProduct::query()->where('supplier_code', '7545/140')->sole();
    $narrow->update(['is_active' => true, 'price_per_meter' => 300]);

    $this->get(route('web.zavesy'))->assertOk()->assertSee('Závěs 7545 – 140 cm');
    $this->get(route('web.zavesy.show', $narrow))
        ->assertOk()
        ->assertSee('Šíře látky')
        ->assertSee('Zpět na závěsy');
    $this->get(route('web.zaclony.show', $narrow))->assertNotFound();

    Livewire::test(TextileProductConfigurator::class, ['product' => $narrow])
        ->assertSee('Závěs s řasící páskou')
        ->set('makeType', 'tape')
        ->set('color', '01')
        ->set('hem', 'lead')
        ->set('split', 'one')
        ->set('width', 200)
        ->set('height', 250)
        ->set('tape', 'siroky70|2')
        ->assertSee('3 pruhy × 2,8 m = 8,4 m')
        ->call('sendToInquiry');

    expect(session('inquiry.configuration.type'))->toBe('drape');
});

it('keeps the roll height and material corrected by the workshop on re-import', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();
    $product->update(['roll_height' => 185, 'material' => 'polyester (ověřeno)']);

    $this->artisan('textile:import-laguna')->assertSuccessful();

    expect($product->refresh())
        ->roll_height->toBe(185)
        ->material->toBe('polyester (ověřeno)');
});

it('does not bring back a supplier color the workshop deleted', function () {
    $this->artisan('textile:import-laguna')->assertSuccessful();
    $product = TextileProduct::query()->where('supplier_code', '11101/180')->sole();

    $this->actingAs(\App\Models\User::factory()->admin()->create())
        ->put(route('admin.textile-pricing.update', $product), [
            'name' => $product->name,
            'category' => 'curtain',
            'roll_height' => 180,
            'colors' => '01',
        ])
        ->assertRedirect();

    $this->artisan('textile:import-laguna')->assertSuccessful();

    expect($product->refresh()->colors->pluck('name')->all())->toBe(['01'])
        ->and($product->excluded_colors)->toBe(['03']);
});

it('refuses supplier files that are not photos', function () {
    expect(fn () => \App\Domain\Suppliers\SupplierImage::store('textile/test/svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'))
        ->toThrow(RuntimeException::class);

    expect(\App\Domain\Suppliers\SupplierImage::store('textile/test/foto', fakeJpeg('x')))->toBe('textile/test/foto.jpg');
});