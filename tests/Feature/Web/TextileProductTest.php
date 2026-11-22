<?php

use App\Domain\Pricing\TextileCurtainSpec;
use App\Domain\Pricing\TextileProductPricer;
use App\Enums\CurtainHem;
use App\Livewire\Web\TextileProductConfigurator;
use App\Models\TextileProduct;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function testCurtain(): TextileProduct
{
    return TextileProduct::query()->where('slug', 'zaclona-test-001')->sole();
}

function configuredCurtain(): \Livewire\Features\SupportTesting\Testable
{
    return Livewire::test(TextileProductConfigurator::class, ['product' => testCurtain()])
        ->set('makeType', 'tape')
        ->set('color', 'Bílá')
        ->set('hem', 'lead')
        ->set('split', 'one')
        ->set('width', 200)
        ->set('height', 250)
        ->set('tape', 'siroky70|2');
}

it('lists the test curtain in the admin textile price list with prices without and with VAT', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.textile-pricing.index'))
        ->assertOk()
        ->assertSeeInOrder(['Název', 'Kategorie', 'Cena bez DPH', 'Cena s DPH'])
        ->assertSeeInOrder(['Záclona test 001', 'Záclona', '300,00 Kč/m', '363,00 Kč/m', 'Upravit']);
});

it('lets the admin edit a textile product', function () {
    $product = testCurtain();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.textile-pricing.update', $product), [
            'name' => 'Záclona test 001',
            'category' => 'curtain',
            'price_per_meter' => 350,
            'roll_height' => 300,
            'colors' => 'Bílá, Šedá',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.textile-pricing.index'));

    expect($product->refresh()->price_per_meter)->toBe(350.0)
        ->and($product->colors()->pluck('name')->all())->toBe(['Bílá', 'Šedá']);
});

it('adds a new textile product with a generated slug', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.textile-pricing.store'), [
            'name' => 'Závěs Len',
            'category' => 'drape',
            'price_per_meter' => 420,
            'roll_height' => 290,
            'colors' => 'Béžová',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.textile-pricing.index'));

    expect(TextileProduct::query()->where('slug', 'zaves-len')->exists())->toBeTrue();
});

it('hides the textile price list from studios', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.textile-pricing.index'))
        ->assertForbidden();
});

it('shows the test curtain on the curtains page and on its own product page', function () {
    $this->get(route('web.zaclony'))
        ->assertOk()
        ->assertSee('Záclona test 001')
        ->assertSee('href="'.route('web.zaclony.show', testCurtain()).'"', false);

    $this->get('/zaclony-a-zavesy/zaclony/zaclona-test-001')
        ->assertOk()
        ->assertSeeInOrder(['Záclona test 001', 'Materiál', '363', 'Kč/m', 'Objednávka šití na míru', 'Typ výrobku']);
});

it('hides inactive products', function () {
    testCurtain()->update(['is_active' => false]);

    $this->get('/zaclony-a-zavesy/zaclony/zaclona-test-001')->assertNotFound();
    $this->get(route('web.zaclony'))->assertDontSee('Záclona test 001');
});

it('prices a fully configured curtain the same as the domain pricer', function () {
    $expected = app(TextileProductPricer::class)->price(
        testCurtain(),
        new TextileCurtainSpec(width: 200, height: 250, hem: CurtainHem::Lead, split: false, ribbonCode: 'siroky70', ratio: 2),
    );

    configuredCurtain()
        ->assertSet('totalPrice', $expected->unitPrice)
        ->assertSet('fabricMeters', 4.2)
        ->assertSee('Látka Záclona test 001 (4,2 m)');
});

it('shows no price until every step is filled', function () {
    Livewire::test(TextileProductConfigurator::class, ['product' => testCurtain()])
        ->set('makeType', 'tape')
        ->set('color', 'Bílá')
        ->assertSet('totalPriceVat', null)
        ->assertSee('Nevybráno');
});

it('walks the steps with continue and rejects an empty step', function () {
    Livewire::test(TextileProductConfigurator::class, ['product' => testCurtain()])
        ->assertSet('step', 'type')
        ->call('next')
        ->assertHasErrors('makeType')
        ->set('makeType', 'tape')
        ->call('next')
        ->assertSet('step', 'color')
        ->call('back')
        ->assertSet('step', 'type');
});

it('rejects a height taller than the roll allows', function () {
    configuredCurtain()
        ->set('height', 300)
        ->assertHasErrors('height')
        ->assertSet('totalPriceVat', null);
});

it('sends products priced on request to the inquiry without a price', function () {
    Livewire::test(TextileProductConfigurator::class, ['product' => testCurtain()])
        ->set('makeType', 'rings')
        ->assertSee('Na poptávku')
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    expect(session('inquiry.configuration.summary'))->toContain('Záclona s kroužky');
});

it('writes the full configuration into the inquiry snapshot', function () {
    configuredCurtain()
        ->set('split', 'two')
        ->call('sendToInquiry')
        ->assertRedirect(route('web.inquiry.create'));

    $snapshot = session('inquiry.configuration');

    expect($snapshot['summary'])->toContain('Záclona test 001')
        ->and($snapshot['summary'])->toContain('Opona – dva díly')
        ->and($snapshot['parameters']['width'])->toEqual(100)
        ->and($snapshot['type'])->toBe('curtain');
});

function textileUpdatePayload(array $extra = []): array
{
    return $extra + [
        'name' => 'Záclona test 001',
        'category' => 'curtain',
        'price_per_meter' => 300,
        'roll_height' => 300,
        'colors' => 'Bílá',
        'is_active' => '1',
    ];
}

it('uploads a product photo in the admin and shows it on the web', function () {
    Storage::fake();
    $product = testCurtain();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.textile-pricing.update', $product), textileUpdatePayload([
            'image' => UploadedFile::fake()->image('zaclona.jpg', 1200, 900),
        ]))
        ->assertRedirect(route('admin.textile-pricing.index'));

    $path = $product->refresh()->image_path;
    Storage::assertExists($path);

    $this->get(route('web.zaclony.show', $product))->assertSee($product->imageUrl(), false);
    $this->get(route('web.zaclony'))->assertSee($product->imageUrl(), false);
    $this->get(route('web.textile-image', $product))->assertOk();
});

it('replaces and removes the product photo', function () {
    Storage::fake();
    $admin = User::factory()->admin()->create();
    $product = testCurtain();

    $this->actingAs($admin)->put(route('admin.textile-pricing.update', $product), textileUpdatePayload([
        'image' => UploadedFile::fake()->image('prvni.jpg'),
    ]));
    $first = $product->refresh()->image_path;

    $this->actingAs($admin)->put(route('admin.textile-pricing.update', $product), textileUpdatePayload([
        'image' => UploadedFile::fake()->image('druha.png'),
    ]));
    Storage::assertMissing($first);
    $second = $product->refresh()->image_path;

    $this->actingAs($admin)->put(route('admin.textile-pricing.update', $product), textileUpdatePayload(['remove_image' => '1']));
    Storage::assertMissing($second);
    expect($product->refresh()->image_path)->toBeNull();
    $this->get(route('web.textile-image', $product))->assertNotFound();
});

it('rejects a file that is not an image', function () {
    Storage::fake();

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.textile-pricing.update', testCurtain()), textileUpdatePayload([
            'image' => UploadedFile::fake()->create('cenik.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('image');
});

it('shows the photo field on the edit form', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.textile-pricing.edit', testCurtain()))
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false)
        ->assertSee('name="image"', false);
});
