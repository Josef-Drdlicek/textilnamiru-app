<?php

use App\Enums\InquiryStatus;
use App\Events\InquirySubmitted;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;

function validInquiryPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jana Nováková',
        'email' => 'jana@example.com',
        'phone' => '+420777123456',
        'city' => 'Brno',
        'space_type' => 'house',
        'products' => ['curtains', 'rails'],
        'measurement' => 'professional',
        'message' => 'Dvě okna v obýváku, jedno v ložnici.',
        'gdpr' => '1',
        'website' => '',
        '_hp_time' => Crypt::encryptString((string) now()->subSeconds(10)->timestamp),
    ], $overrides);
}

it('stores an inquiry and dispatches the event', function () {
    Event::fake([InquirySubmitted::class]);

    $this->post('/poptavka', validInquiryPayload())
        ->assertRedirect(route('web.inquiry.thanks'));

    $inquiry = Inquiry::sole();

    expect($inquiry->status)->toBe(InquiryStatus::New)
        ->and($inquiry->name)->toBe('Jana Nováková')
        ->and($inquiry->products->map(fn ($product) => $product->value)->all())->toBe(['curtains', 'rails'])
        ->and($inquiry->gdpr_consented_at)->not->toBeNull();

    Event::assertDispatched(InquirySubmitted::class, fn ($event) => $event->inquiry->is($inquiry));
});

it('validates required fields', function () {
    $this->from('/poptavka')
        ->post('/poptavka', validInquiryPayload([
            'name' => '', 'email' => 'neni-email', 'phone' => '', 'measurement' => null, 'gdpr' => null,
        ]))
        ->assertRedirect('/poptavka')
        ->assertSessionHasErrors(['name', 'email', 'phone', 'measurement', 'gdpr']);

    expect(Inquiry::count())->toBe(0);
});

it('rejects unknown product values', function () {
    $this->post('/poptavka', validInquiryPayload(['products' => ['vrtulnik']]))
        ->assertSessionHasErrors('products.0');
});

it('silently discards submissions with a filled honeypot field', function () {
    Event::fake([InquirySubmitted::class]);

    $this->post('/poptavka', validInquiryPayload(['website' => 'https://spam.example']))
        ->assertRedirect(route('web.inquiry.thanks'));

    expect(Inquiry::count())->toBe(0);
    Event::assertNotDispatched(InquirySubmitted::class);
});

it('silently discards submissions filled faster than a human could', function () {
    $this->post('/poptavka', validInquiryPayload([
        '_hp_time' => Crypt::encryptString((string) now()->timestamp),
    ]))
        ->assertRedirect(route('web.inquiry.thanks'));

    expect(Inquiry::count())->toBe(0);
});

it('silently discards submissions with a tampered time trap', function () {
    $this->post('/poptavka', validInquiryPayload(['_hp_time' => 'padelek']))
        ->assertRedirect(route('web.inquiry.thanks'));

    expect(Inquiry::count())->toBe(0);
});

it('attaches the calculator configuration from the session and clears it', function () {
    $configuration = [
        'type' => 'pillow',
        'parameters' => ['width' => 50.0, 'height' => 50.0, 'stuffing' => true, 'zipper' => 'short'],
        'count' => 2,
        'total_price_vat' => 266.2,
        'summary' => 'Polštář · 50 × 50 cm · 2 ks · včetně výplně',
    ];

    $this->withSession(['inquiry.configuration' => $configuration])
        ->post('/poptavka', validInquiryPayload())
        ->assertRedirect(route('web.inquiry.thanks'));

    expect(Inquiry::sole()->configuration)->toEqual($configuration)
        ->and(session('inquiry.configuration'))->toBeNull();
});

it('shows the configuration summary on the inquiry form', function () {
    $this->withSession(['inquiry.configuration' => ['type' => 'pillow', 'summary' => 'Polštář · 50 × 50 cm · 2 ks', 'total_price_vat' => 266.0]])
        ->get('/poptavka')
        ->assertOk()
        ->assertSee('Polštář · 50 × 50 cm · 2 ks');
});

it('removes the configuration on request', function () {
    $this->withSession(['inquiry.configuration' => ['type' => 'pillow', 'summary' => 'Polštář']])
        ->get('/poptavka?bez-konfigurace=1')
        ->assertRedirect(route('web.inquiry.create'));

    expect(session('inquiry.configuration'))->toBeNull();
});

it('preselects products from the query string', function () {
    $this->get('/poptavka?produkt=rails')
        ->assertOk()
        ->assertViewHas('preselectedProducts', ['rails']);
});

it('throttles repeated submissions from one ip', function () {
    foreach (range(1, 3) as $i) {
        $this->post('/poptavka', validInquiryPayload(['email' => "test{$i}@example.com"]));
    }

    $this->post('/poptavka', validInquiryPayload())
        ->assertStatus(429);
});
