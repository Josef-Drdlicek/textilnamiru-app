<?php

use App\Domain\Pricing\RailPricer;
use App\Domain\Suppliers\Detede\DetedeImporter;
use App\Enums\RailKind;
use App\Enums\RailMounting;
use App\Models\RailImage;
use App\Models\RailProduct;
use App\Models\RailVariant;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake();
    Http::fake(fn (Request $request) => Http::response(fakeJpeg(parse_url($request->url(), PHP_URL_PATH))));
});

function smallDetedeCatalog(): string
{
    $path = tempnam(sys_get_temp_dir(), 'detede');
    file_put_contents($path, json_encode(['products' => [
        [
            'kind' => 'rail', 'line' => 'T-Line', 'name' => 'T1 Torino', 'code' => 'T1', 'subtitle' => 'Nejpoužívanější kolejnice',
            'source' => 'https://www.detede.net/t-line', 'mountings' => ['ceiling', 'wall'],
            'features' => ['Univerzální kolejnice'], 'specs' => ['Profil' => '13,1 × 13,9 mm'],
            'images' => [['id' => 't1~mv2.png', 'alt' => 'T1 Torino']],
            'components' => [['code' => 'JS-01', 'name' => 'Jezdec standard', 'image' => 'js01~mv2.png', 'group' => 'Jezdci a držáky']],
            'gallery' => ['gal1~mv2.jpg'],
            'variants' => [
                ['name' => 'Jednokolejnička rovná', 'code' => 'T1-11', 'images' => [['id' => 't111~mv2.png', 'alt' => 'T1-11']]],
                ['name' => 'Dvoukolejnička rovná', 'code' => 'T1-21', 'images' => [['id' => 't121~mv2.png', 'alt' => 'T1-21']]],
            ],
        ],
        [
            'kind' => 'rail', 'line' => 'S-Line', 'name' => 'S1 Siena', 'code' => 'S1', 'mountings' => ['ceiling'],
            'images' => [['id' => 's1~mv2.png', 'alt' => 'S1 Siena']],
            'components' => [['code' => 'JS-01', 'name' => 'Jezdec standard', 'image' => 'js01~mv2.png', 'group' => 'Jezdci a držáky']],
            'variants' => [['name' => 'Základní provedení']],
        ],
        [
            'kind' => 'decorative', 'name' => 'Quadro 35', 'subtitle' => 'Dekorační garnýž', 'overview' => 'quadro~mv2.png',
            'mountings' => ['ceiling', 'wall'], 'specs' => ['Profil' => '35 mm'], 'features' => ['Montáž na stěnu i strop'],
            'components' => [['code' => null, 'name' => 'Mezistěnové upevnění', 'image' => 'mezi~mv2.png', 'group' => 'Montáž']],
            'variants' => [
                ['name' => 'Bílá', 'images' => [['id' => 'bila~mv2.png', 'alt' => 'Quadro 35 – bílá']]],
                ['name' => 'Černá', 'images' => [['id' => 'cerna~mv2.png', 'alt' => 'Quadro 35 – černá']]],
            ],
        ],
    ]], JSON_THROW_ON_ERROR));

    return $path;
}

it('imports every DeTeDe rail type as its own product with its assemblies', function () {
    $this->artisan('rails:import-detede --no-images')
        ->expectsOutputToContain('Hotovo: 21 produktů')
        ->assertSuccessful();

    expect(RailProduct::query()->where('kind', RailKind::Rail)->orderBy('sort_order')->pluck('name')->all())->toBe([
        'N1 Napoli', 'N2 Nolina', 'N3 Nola', 'T1 Torino', 'T2 Terme', 'T3 Trento', 'T9 Trentino',
        'B1 Basso', 'B2 Bergamo', 'B3 Bolzano', 'S1 Siena', 'S2 Savona', 'E2 Empoli', 'E3 Extra', 'H1 Hospital',
    ])
        ->and(RailProduct::query()->where('kind', RailKind::Decorative)->orderBy('sort_order')->pluck('name')->all())
        ->toBe(['Quadro 35', 'Deco 28', 'Deco 20', 'Libero 20', 'Gavia 19', 'Rustik 19'])
        ->and(RailVariant::query()->count())->toBe(46)
        ->and(RailProduct::query()->where('is_active', true)->count())->toBe(0);

    $torino = RailProduct::query()->where('slug', 't1-torino')->sole();
    expect($torino)
        ->line->toBe('T-Line')
        ->code->toBe('T1')
        ->and($torino->variants->pluck('code')->all())->toBe(['T1-11', 'T1-12', 'T1-21', 'T1-22'])
        ->and($torino->mountingOptions())->toBe([RailMounting::Ceiling, RailMounting::Wall])
        ->and($torino->specs[0])->toBe(['label' => 'Profil', 'value' => '13,1 × 13,9 mm']);

    $napoli = RailProduct::query()->where('slug', 'n1-napoli')->sole();
    // Detede sestavy N1 neuvádí – podle majitele stejné jako u T1.
    expect($napoli->variants->pluck('code')->all())->toBe(['N1-11', 'N1-12', 'N1-21', 'N1-22'])
        ->and(collect($napoli->components)->pluck('code')->all())->toContain('JS-01', 'JT-03', 'N1-03');

    expect(RailProduct::query()->where('slug', 's1-siena')->sole()->mountingOptions())->toBe([RailMounting::Ceiling])
        ->and(RailProduct::query()->where('slug', 'b1-basso')->sole()->paragraphs())->toHaveCount(3);
});

it('replaces the former rail lines and keeps prices and visibility on re-import', function () {
    $oldLine = RailProduct::query()->create(['kind' => RailKind::Rail, 'name' => 'T-Line', 'slug' => 't-line', 'supplier' => 'detede']);
    $oldLine->variants()->create(['name' => 'T1 Torino']);
    $pricedLine = RailProduct::query()->create(['kind' => RailKind::Rail, 'name' => 'N-Line', 'slug' => 'n-line', 'supplier' => 'detede', 'is_active' => true]);
    $pricedLine->variants()->create(['name' => 'N1 Napoli'])->prices()->create(['length_to' => 50, 'price' => 100]);

    $importer = app(DetedeImporter::class);
    expect($importer->import(catalogPath: smallDetedeCatalog()))->toBe(3)
        ->and($importer->removed)->toBe(1)
        ->and(RailProduct::query()->where('slug', 't-line')->exists())->toBeFalse()
        ->and($pricedLine->refresh()->is_active)->toBeFalse()
        ->and($importer->warnings)->toContain('N-Line: v katalogu už není, ale má vyplněné ceny – jen jsem ho skryl.');

    $torino = RailProduct::query()->where('slug', 't1-torino')->sole();
    $photo = RailImage::query()->where('rail_product_id', $torino->id)->whereNull('rail_variant_id')->where('role', 'photo')->orderBy('sort_order')->first();
    expect($photo->source_url)->toBe(DetedeImporter::IMAGE_BASE.'t1~mv2.png')
        ->and(Storage::get($photo->image_path))->toBe(fakeJpeg('/media/4586fe_t1~mv2.png/v1/fit/w_1600,h_1600,q_90/t1~mv2.png'))
        // Stejný jezdec u dvou kolejnic = jedna stažená fotka.
        ->and($importer->images)->toBe(10);

    $sestava = $torino->variants->first();
    $sestava->prices()->create(['mounting' => 'wall', 'length_to' => 100, 'price' => 450]);
    $torino->update(['is_active' => true]);

    $again = app(DetedeImporter::class);
    $again->import(catalogPath: smallDetedeCatalog());

    expect($again->images)->toBe(0)
        ->and($again->removed)->toBe(0)
        ->and($sestava->prices()->count())->toBe(1)
        ->and($torino->refresh()->is_active)->toBeTrue();
});

it('prices a rail by assembly, mounting and length in 50 cm bands up to 6 m', function () {
    app(DetedeImporter::class)->import(false, catalogPath: smallDetedeCatalog());
    $variant = RailVariant::query()->where('code', 'T1-11')->sole();
    $variant->prices()->createMany([
        ['mounting' => 'ceiling', 'length_to' => 50, 'price' => 300],
        ['mounting' => 'wall', 'length_to' => 50, 'price' => 340],
        ['mounting' => 'ceiling', 'length_to' => 150, 'price' => 520],
    ]);
    $pricer = app(RailPricer::class);

    expect(RailPricer::bands())->toHaveCount(12)
        ->and(RailPricer::bandLabel(150))->toBe('101–150 cm')
        ->and($pricer->price($variant, RailMounting::Ceiling, 1)->unitPrice)->toBe(300.0)
        ->and($pricer->price($variant, RailMounting::Wall, 50)->unitPrice)->toBe(340.0)
        ->and($pricer->price($variant, RailMounting::Ceiling, 101)->unitPrice)->toBe(520.0)
        ->and($pricer->price($variant, RailMounting::Wall, 101))->toBeNull()
        ->and($pricer->price($variant, RailMounting::Ceiling, 150)->lines[0]->label)->toBe('T1 Torino – Jednokolejnička rovná, stropní držák (101–150 cm)')
        ->and($pricer->price($variant, RailMounting::Ceiling, 601))->toBeNull();
});

it('lets the admin fill prices per assembly and mounting and publish a rail', function () {
    app(DetedeImporter::class)->import(false, catalogPath: smallDetedeCatalog());
    $torino = RailProduct::query()->where('slug', 't1-torino')->sole();
    [$single, $double] = $torino->variants;
    $siena = RailProduct::query()->where('slug', 's1-siena')->sole();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.rail-pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Kolejnice', 'T-Line', 'T1 Torino', 'T1-11', 'Stropní držák', 'Stěnový držák', 'T1-21', 'S1 Siena', 'Dekorační garnýže', 'Quadro 35', 'Bílá'])
        ->assertSee('551–600 cm')
        ->assertSee('name="prices['.$single->id.'][wall][600]"', false)
        ->assertDontSee('name="prices['.$siena->variants->first()->id.'][wall][50]"', false);

    $this->actingAs($admin)
        ->put(route('admin.rail-pricing.update', $torino), ['prices' => [
            $single->id => ['ceiling' => [50 => '300', 100 => '', 75 => '999'], 'wall' => [50 => '340', 600 => '1490.5']],
            $double->id => ['ceiling' => [50 => '500'], 'side' => [50 => '1']], // „side“ není držák
            999999 => ['ceiling' => [50 => '1']], // cizí sestava
        ]])
        ->assertRedirect(route('admin.rail-pricing.index').'#produkt-t1-torino');

    expect($single->prices()->where('mounting', 'ceiling')->pluck('price', 'length_to')->all())->toBe([50 => 300.0])
        ->and($single->prices()->where('mounting', 'wall')->pluck('price', 'length_to')->all())->toBe([50 => 340.0, 600 => 1490.5])
        ->and($double->prices()->count())->toBe(1);

    // S1 jde jen do stropu – stěnový ceník se neuloží.
    $sienaVariant = $siena->variants->first();
    $this->actingAs($admin)
        ->put(route('admin.rail-pricing.update', $siena), ['prices' => [$sienaVariant->id => ['wall' => [50 => '200'], 'ceiling' => [50 => '210']]]]);
    expect($sienaVariant->prices()->get()->map(fn ($price) => $price->mounting->value)->all())->toBe(['ceiling']);

    $this->actingAs($admin)->patch(route('admin.rail-pricing.toggle', $torino))->assertRedirect();
    expect($torino->refresh()->is_active)->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.rail-pricing.update', $torino), ['prices' => []])
        ->assertForbidden();
});

it('shows published rails and decorative rods on the hanging page with details', function () {
    app(DetedeImporter::class)->import(catalogPath: smallDetedeCatalog());
    $torino = RailProduct::query()->where('slug', 't1-torino')->sole();
    $quadro = RailProduct::query()->where('slug', 'quadro-35')->sole();

    $this->get(route('web.zaveseni-textilu'))->assertOk()->assertDontSee('T1 Torino');
    $this->get(route('web.zaveseni.show', $torino))->assertNotFound();

    $torino->update(['is_active' => true]);
    $quadro->update(['is_active' => true]);
    $torino->variants[1]->prices()->createMany([
        ['mounting' => 'ceiling', 'length_to' => 100, 'price' => 400],
        ['mounting' => 'wall', 'length_to' => 100, 'price' => 440],
    ]);

    $this->get(route('web.zaveseni-textilu'))
        ->assertOk()
        ->assertSeeInOrder(['Kolejnice', 'T-Line', 'T1 Torino', 'Dekorační garnýže', 'Quadro 35'])
        ->assertSee('2 sestav')
        ->assertSee('2 barevných provedení');

    $this->get(route('web.zaveseni.show', [$torino, 'varianta' => 1]))
        ->assertOk()
        ->assertSee('Sestava')
        ->assertSee('T1-21')
        ->assertSee('aria-current="page"', false)
        ->assertSee('Řada T-Line')
        ->assertSee('JS-01')
        ->assertSee('stropní držák, stěnový držák')
        ->assertSee('od 484')
        ->assertSeeInOrder(['51–100 cm', '484 Kč', '532 Kč']);

    $this->get(route('web.zaveseni.show', $quadro))
        ->assertOk()
        ->assertSee('Barva')
        ->assertSee('35 mm')
        ->assertSee('Mezistěnové upevnění')
        ->assertSee('Cena podle délky');

    $image = RailImage::query()->whereNotNull('image_path')->first();
    $this->get(route('web.rail-image', $image))->assertOk();
});
