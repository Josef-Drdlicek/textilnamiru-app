<?php

use App\Domain\Pricing\ShadePricer;
use App\Domain\Suppliers\Pavon\PavonCatalog;
use App\Domain\Suppliers\Pavon\PavonPageParser;
use App\Models\ShadeFabric;
use App\Models\ShadeProduct;
use App\Models\ShadeVariant;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Stránka ve stejné struktuře jako produktové stránky pavon.cz (ověřeno 22. 11. 2026).
 */
function pavonPage(string $name, string $dimensions = '<li>maximální šířka: 250 cm</li>'): string
{
    return <<<HTML
<html><head><meta charset="utf-8"></head><body>
<div id="content" class="container">
  <img class="img-responsive w100" alt="{$name}_01" src="https://www.pavon.cz/upload/4344-1410575782_m.jpg">
  <img class="img-responsive w100" alt="{$name}_02" src="https://www.pavon.cz/upload/4345-1410431816_m.jpg">
  <div id="zakladni-informace" class="tab-pane fade in active">
    <h1 class="nadpis text-center"><strong>{$name}</strong></h1>
    <h2 class="sub_nadpis text-center"><strong>roleta s řetízkovým mechanismem</strong></h2>
    <div class="product-detail-text text-center">
      <p><em style="color: #ee9b13;">»&nbsp;moderní, praktická a cenově výhodná&nbsp;«</em>&nbsp;</p>
      <p>Roleta s&nbsp;řetízkovým ovládáním je oblíbená zejména pro snadný a pohodlný způsob svého ovládání.</p>
      <p>Rolety vyrábíme z&nbsp;kvalitních a funkčních materiálů v desítkách barevných odstínů.</p>
      <div>Hledáte originální design? Podívejte se na náš <a href="#">KATALOG POTISKOVÝCH VZORŮ</a>.</div>
      <div><span><strong>Ceny již od 933 Kč (1 129 Kč s DPH)</strong></span></div>
    </div>
  </div>
  <div id="technicke-informace" class="tab-pane fade">
    <div class="indiv_popis"><h3 class="alignL">{$name}</h3> <h5>&nbsp;</h5>
      <h4>Standardní provedení rolety zahrnuje:</h4>
      <ul><li><span>duralový samotoč s řetízkovým mechanismem Ø 28mm</span></li><li>stínicí textilii</li></ul>
      <p>&nbsp;</p>
      <h4><a href="#">Nadstandardní a příplatkové provedení rolety:</a></h4>
      <ul><li>montážní profil s klipy</li><li class="zvyraznit_text"><strong>motorová verze rolety</strong></li><ul><li>s motorem AM 25QE</li></ul></ul>
      <h4>Standardní rozměry rolety:</h4>
      <ul>{$dimensions}</ul>
      <p class="mensi_text"><em>Nestandardní rozměry rolet Vám vyrobíme pouze po předchozí konzultaci.</em></p>
      <h4>Umístění rolety:</h4>
      <table><tbody><tr><td>A) na rám okna</td><td>B) na stěnu</td></tr><tr><td><img src="https://www.pavon.cz/upload/1046-1206278039_s.png"></td></tr></tbody></table>
    </div>
  </div>
  <div id="galerie" class="tab-pane fade"><img src="https://www.pavon.cz/upload/2510-1504055310_m.jpg" alt=""><img src="../upload/2361-1301481307_v.jpg" alt="Detail"></div>
</div></body></html>
HTML;
}

function fakePavon(): void
{
    Http::fake(function (Request $request) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if (str_starts_with($path, '/upload/')) {
            return Http::response(fakeJpeg($path));
        }
        if ($path === '/rolety/maestra') {
            return Http::response(pavonPage('Roleta Maestra', '<li>maximální šířka: 170 cm</li><li>maximální výška: 250 cm</li>'));
        }
        if ($path === '/rolety/espiral') {
            return Http::response(pavonPage('Roleta Espiral', '<li>minimální šířka: 40 cm</li><li>maximální šířka: 250 cm</li>'));
        }

        return Http::response(pavonPage('Roleta'));
    });
}

beforeEach(function () {
    Storage::fake();
    fakePavon();
});

it('imports the chosen Pavon blinds as products with variants, texts, sizes and photos', function () {
    $this->artisan('shades:import-pavon')->assertSuccessful();

    expect(ShadeProduct::query()->orderBy('sort_order')->pluck('name')->all())->toBe(array_keys(PavonCatalog::PRODUCTS))
        ->and(ShadeVariant::query()->count())->toBe(12);

    $leontina = ShadeProduct::query()->where('slug', 'roleta-leontina')->sole();
    expect($leontina->is_active)->toBeFalse()
        ->and($leontina->variants->pluck('name')->all())->toBe(['Leontina', 'Leontina Box (v kazetě)']);

    $magic = ShadeProduct::query()->where('slug', 'roleta-maestra-magic')->sole();
    expect($magic->variants->pluck('name')->all())->toBe(['Maestra Magic', 'Maestra Magic Profil (nalepovací)', 'Magic Open (bez kazety)', 'Magic Box (v kazetě)']);

    $variant = $leontina->variants->first();
    expect($variant)
        ->subtitle->toBe('roleta s řetízkovým mechanismem')
        ->slogan->toBe('moderní, praktická a cenově výhodná')
        ->and($variant->paragraphs())->toHaveCount(2)
        ->and($variant->description)->not->toContain('KATALOG')->not->toContain('Ceny již od')
        ->and($variant->technical)->toContain(['type' => 'heading', 'text' => 'Standardní provedení rolety zahrnuje'])
        ->and($variant->technical)->toContain(['type' => 'list', 'items' => ['montážní profil s klipy', 'motorová verze rolety', '– s motorem AM 25QE']])
        ->and($variant->technical)->toContain(['type' => 'list', 'items' => ['A) na rám okna', 'B) na stěnu']]);

    // Fotky v plném rozlišení (bez _m/_v), stažené k nám.
    expect($variant->images->pluck('source_url')->all())->toBe([
        'https://www.pavon.cz/upload/4344-1410575782.jpg',
        'https://www.pavon.cz/upload/4345-1410431816.jpg',
        'https://www.pavon.cz/upload/2510-1504055310.jpg',
        'https://www.pavon.cz/upload/2361-1301481307.jpg',
    ]);
    expect(Storage::get($variant->images->first()->image_path))->toBe(fakeJpeg('/upload/4344-1410575782.jpg'));

    // Rozměry z technických informací, chybějící výška = 250 cm.
    // Z jakých látek se provedení šije.
    expect($magic->variants->first()->fabric_collections)->toBe(['magico'])
        ->and(ShadeVariant::query()->where('name', 'Roma')->sole()->fabric_collections)->toBe(['roma'])
        ->and($variant->fabric_collections)->toBe(['standard', 'screen']);

    $maestra = ShadeVariant::query()->where('name', 'Maestra')->sole();
    expect([$maestra->min_width, $maestra->max_width, $maestra->min_height, $maestra->max_height])->toBe([40, 170, 40, 250]);
    expect(ShadeVariant::query()->where('name', 'Espiral')->sole()->max_width)->toBe(250);
});

it('keeps prices, sizes and visibility when the import runs again', function () {
    $this->artisan('shades:import-pavon')->assertSuccessful();
    $variant = ShadeVariant::query()->where('name', 'Bruno')->sole();
    $variant->update(['max_height' => 300]);
    $variant->prices()->create(['width' => 100, 'height' => 100, 'price' => 1200]);
    $variant->product->update(['is_active' => true]);

    $this->artisan('shades:import-pavon')->assertSuccessful();

    $variant->refresh();
    expect(ShadeVariant::query()->count())->toBe(12)
        ->and($variant->max_height)->toBe(300)
        ->and($variant->prices()->count())->toBe(1)
        ->and($variant->product->is_active)->toBeTrue()
        ->and($variant->images()->count())->toBe(4);
});

it('looks up the price for a window size rounding up to the next cell', function () {
    $this->artisan('shades:import-pavon --no-images')->assertSuccessful();
    $variant = ShadeVariant::query()->where('name', 'Bruno')->sole();
    $variant->prices()->createMany([
        ['width' => 100, 'height' => 100, 'price' => 1000],
        ['width' => 110, 'height' => 100, 'price' => 1100],
        ['width' => 110, 'height' => 110, 'price' => 1200],
    ]);

    $variant->prices()->create(['price_category' => 'C', 'width' => 100, 'height' => 100, 'price' => 1500]);
    $fabricA = ShadeFabric::query()->create(['code' => 'Col 100', 'name' => 'Col 100', 'slug' => 'col-100', 'collection' => 'standard', 'price_category' => 'A', 'roll_width' => 200]);
    $fabricC = ShadeFabric::query()->create(['code' => 'Juno 1', 'name' => 'Juno 1', 'slug' => 'juno-1', 'collection' => 'standard', 'price_category' => 'C', 'roll_width' => 102]);

    $pricer = app(ShadePricer::class);
    $price = fn (ShadeFabric $fabric, float $width, float $height) => $pricer->price($variant, $fabric, $width, $height)?->unitPrice;

    expect($price($fabricA, 100, 100))->toBe(1000.0)
        ->and($price($fabricA, 101, 95))->toBe(1100.0)
        ->and($price($fabricA, 105, 105))->toBe(1200.0)
        ->and($price($fabricA, 120, 120))->toBeNull()
        ->and($price($fabricA, 300, 100))->toBeNull()
        // Skupina C má vlastní tabulku; látka šíře 102 cm na širší roletu nestačí.
        ->and($price($fabricC, 100, 100))->toBe(1500.0)
        ->and($price($fabricC, 101, 95))->toBeNull()
        ->and($pricer->price($variant, $fabricA, 101, 95)->lines[0]->label)->toBe('Roleta Bruno, látka Col 100 – skupina A (ceník do 110 × 100 cm)');
});

it('lets the admin fill and save the price grid', function () {
    $this->artisan('shades:import-pavon --no-images')->assertSuccessful();
    $variant = ShadeVariant::query()->where('name', 'Rolsun')->sole();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.shade-pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Roleta Leontina', 'Leontina', 'Leontina Box (v kazetě)', 'Roleta Maestra Magic', 'Magic Box (v kazetě)']);

    $this->actingAs($admin)
        ->get(route('admin.shade-pricing.edit', $variant))
        ->assertOk()
        ->assertSee('name="prices[40][40]"', false)
        ->assertSee('name="prices[140][120]"', false);

    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.prices', $variant), ['price_category' => 'A', 'prices' => [
            40 => [40 => '800', 50 => ''],
            50 => [50 => '850.5'],
            55 => [40 => '999'], // mimo mřížku – zahodí se
        ]])
        ->assertRedirect(route('admin.shade-pricing.edit', [$variant, 'skupina' => 'A']));

    // Skupina B se ukládá zvlášť a skupinu A nepřepíše.
    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.prices', $variant), ['price_category' => 'B', 'prices' => [40 => [40 => '900']]])
        ->assertRedirect(route('admin.shade-pricing.edit', [$variant, 'skupina' => 'B']));

    expect($variant->prices()->where('price_category', 'A')->orderBy('price')->pluck('price')->all())->toBe([800.0, 850.5])
        ->and($variant->prices()->where('price_category', 'B')->pluck('price')->all())->toBe([900.0]);

    $this->actingAs($admin)
        ->get(route('admin.shade-pricing.edit', [$variant, 'skupina' => 'B']))
        ->assertOk()
        ->assertSee('Cenová skupina D')
        ->assertSee('name="price_category" value="B"', false)
        ->assertSee('value="900"', false)
        ->assertDontSee('value="800"', false);

    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.prices', $variant), ['price_category' => 'E', 'prices' => []])
        ->assertSessionHasErrors('price_category');

    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.settings', $variant), ['min_width' => 40, 'max_width' => 130, 'min_height' => 40, 'max_height' => 150, 'step' => 10, 'fabric_collections' => ['standard']])
        ->assertRedirect(route('admin.shade-pricing.edit', [$variant, 'skupina' => 'A']));
    expect($variant->refresh()->widths())->toBe([40, 50, 60, 70, 80, 90, 100, 110, 120, 130])
        ->and($variant->fabric_collections)->toBe(['standard']);

    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.settings', $variant), ['min_width' => 10, 'max_width' => 1000, 'min_height' => 10, 'max_height' => 1000, 'step' => 5, 'fabric_collections' => ['standard']])
        ->assertSessionHasErrors('step');

    $this->actingAs($admin)
        ->put(route('admin.shade-pricing.settings', $variant), ['min_width' => 40, 'max_width' => 130, 'min_height' => 40, 'max_height' => 150, 'step' => 10, 'fabric_collections' => []])
        ->assertSessionHasErrors('fabric_collections');
});

it('hides the blinds price list from studios', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.shade-pricing.index'))
        ->assertForbidden();
});

it('shows a hidden blind only to the admin and lists published ones on the blinds page', function () {
    $this->artisan('shades:import-pavon')->assertSuccessful();
    $magic = ShadeProduct::query()->where('slug', 'roleta-maestra-magic')->sole();

    $this->get(route('web.rolety.show', $magic))->assertNotFound();
    $this->get(route('web.rolety'))->assertDontSee('Roleta Maestra Magic');

    $magic->update(['is_active' => true]);
    $magic->variants->get(3)->prices()->create(['width' => 100, 'height' => 100, 'price' => 1000]);

    $this->get(route('web.rolety'))->assertSee('Roleta Maestra Magic')->assertSee('4 provedení');
    $this->get(route('web.rolety.show', [$magic, 'varianta' => 3]))
        ->assertOk()
        ->assertSee('Magic Box (v kazetě)')
        ->assertSee('aria-current="page"', false)
        ->assertSee('Technické informace')
        ->assertSee('od 1 210')
        ->assertSee('data-textile-main', false);

    $image = $magic->variants->first()->images->first();
    $this->get(route('web.shade-image', $image))->assertOk();
});

it('reads Pavon dimensions in every wording', function () {
    $page = (new PavonPageParser)->parse(pavonPage('X', '<li>minimální šířka: 30 cm</li><li>maximální šířka: 120 cm</li><li>maximální délka: 140 cm</li>'));

    expect([$page->minWidth, $page->maxWidth, $page->maxHeight])->toBe([30, 120, 140]);
});
