<?php

use App\Models\User;

it('renders the public homepage for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Interiérový textil šitý')
        ->assertSee('Textil, stropy i stínění z jedné dílny')
        ->assertSee('Kolik to bude stát?')
        ->assertSee('Rodinná tradice od roku 1992')
        ->assertSee('Vypínaný stropní podhled v interiéru')
        ->assertSee('Stínicí technika v interiéru')
        ->assertSee('Vypínaný stropní podhled')
        ->assertSee('Nová technologie stropů')
        ->assertSee('Stínění pro každou situaci');
});

it('links the header B2B button to the b2b page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('>B2B</a>', false)
        ->assertSee('Přihlášení pro studia (B2B)');
});

it('does not repeat full catalog, calculator or gallery on the homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Otevřít kalkulačku')
        ->assertDontSee('za ušití vč. DPH, bez látky')
        ->assertDontSee('Hotová šířka')
        ->assertDontSee('Hotel Voroněž, Brno');
});

it('renders the homepage for authenticated users without redirecting', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('Textil, stropy i stínění z jedné dílny');
});

it('renders the calculator hub with three tiles', function () {
    $this->get('/kalkulacka')
        ->assertOk()
        ->assertSee('Kalkulačka')
        ->assertSee('Zakázkové šití')
        ->assertSee('Vypínané stropy')
        ->assertSee('Stínicí technika')
        ->assertDontSee('DOPLNIT')
        ->assertDontSee('Kolik bude stát ušití?')
        ->assertDontSee('Záclona');
});

it('renders the sewing calculator on the subpage', function () {
    $this->get('/kalkulacka/zakazkove-sivani')
        ->assertOk()
        ->assertSee('Kolik bude stát ušití?')
        ->assertSee('Orientační kalkulačka')
        ->assertSee('Záclona')
        ->assertSee('Zpět na výběr kalkulace');
});

it('renders the stretch ceiling calculator with a live price', function () {
    $this->get('/kalkulacka/vypinane-stropy')
        ->assertOk()
        ->assertSee('Kolik bude stát vypínaný strop?')
        ->assertSee('Vypínaný strop')
        ->assertSee('Zpět na výběr kalkulace')
        ->assertDontSee('Záclona')
        ->assertDontSee('DOPLNIT');
});
it('redirects the old calculator type query to the sewing subpage', function () {
    $this->get('/kalkulacka?typ=curtain')
        ->assertRedirect(route('web.kalkulacka.sivani', ['typ' => 'curtain']));
});

it('renders the gallery page', function () {
    $this->get('/realizace')
        ->assertOk()
        ->assertSee('Naše realizace')
        ->assertSee('Hotel Voroněž, Brno')
        ->assertSee('Zámek Emy Destinové');
});

it('renders the stretched-ceiling page', function () {
    $this->get('/vypinany-podhled')
        ->assertOk()
        ->assertSee('Napínané stropy – dokonalý vzhled vašeho interiéru')
        ->assertSee('Podsvícený vypínaný strop se světelným obvodem')
        ->assertSee('images/web/podhled-1.webp', false)
        ->assertSee(route('web.kalkulacka.stropy'), false)
        ->assertSee('Předchozí obrázek')
        ->assertSee('Další obrázek')
        ->assertSee('Co jsou napínané stropy?')
        ->assertSee('Hlavní výhody napínaných stropů')
        ->assertSee('Varianty provedení')
        ->assertSee('Kde lze napínané stropy využít?');
});

it('renders the shading-technology page with products and a call to action', function () {
    $this->get('/stinici-technika')
        ->assertOk()
        ->assertSee('Stínicí technika')
        ->assertSee('Japonské stěny')
        ->assertSee(route('web.inquiry.create', ['produkt' => 'shades']), false)
        ->assertDontSee('DOPLNIT')
        ->assertDontSee('noindex', false);
});

it('redirects the retired shading calculator url to the inquiry form', function () {
    $this->get('/kalkulacka/stinici-technika')
        ->assertRedirect('/poptavka?produkt=shades')
        ->assertStatus(301);
});

it('serves responsive hero images with srcset and a transparent logo', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('images/web/hero-960.webp 960w', false)
        ->assertSee('images/web/hero-2400.webp 2400w', false)
        ->assertSee('images/logo-144.png 2x', false)
        ->assertDontSee('logo.jpg', false);
});

it('renders the b2b login page with noindex', function () {
    $this->get('/b2b')
        ->assertOk()
        ->assertSee('B2B přihlášení')
        ->assertSee('Přihlášení pro interiérová studia')
        ->assertSee('noindex', false)
        ->assertSee('Přihlásit se')
        ->assertSee('name="username"', false)
        ->assertSee('action="'.route('login.store').'"', false);
});

it('redirects the former catalog page to curtains and drapes', function () {
    $this->get('/sortiment')->assertStatus(301)->assertRedirect('/zaclony-a-zavesy');

    $this->get('/')
        ->assertOk()
        ->assertDontSee('/sortiment', false)
        ->assertSee('href="'.route('web.kalkulacka.sivani').'"', false);
});

it('renders the new placeholder pages', function (string $url, string $title) {
    $this->get($url)
        ->assertOk()
        ->assertSee($title)
        ->assertSee('Nezávazná poptávka');
})->with([
    ['/zaclony-a-zavesy', 'Záclony a závěsy'],
    ['/zaveseni-textilu', 'Zavěšení textilu'],
]);

it('shows two big tiles leading to curtains and drapes', function () {
    $this->get('/zaclony-a-zavesy')
        ->assertOk()
        ->assertSeeInOrder([
            'href="'.route('web.zaclony').'"', 'Záclony',
            'href="'.route('web.zavesy').'"', 'Závěsy',
        ], false);
});

it('renders the curtain and drape pages with a link back', function (string $url, string $title) {
    $this->get($url)
        ->assertOk()
        ->assertSee($title)
        ->assertSee('Zpět na záclony a závěsy')
        ->assertSee('Nezávazná poptávka');
})->with([
    ['/zaclony-a-zavesy/zaclony', 'Záclony'],
    ['/zaclony-a-zavesy/zavesy', 'Závěsy'],
]);

it('puts the new pages first in the main menu, before the stretch ceiling', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['>Záclony a závěsy</a>', '>Zavěšení textilu</a>', '>Vypínaný podhled</a>', '>Stínicí technika</a>'], false);
});

it('leads from the blinds tile on the shading page to the new blinds page', function () {
    $this->get(route('web.stinici-technika'))
        ->assertOk()
        ->assertSee('href="'.route('web.rolety').'"', false)
        ->assertSee('Více o roletách');

    $this->get('/stinici-technika/rolety')
        ->assertOk()
        ->assertSee('Rolety')
        ->assertSee('Zpět na stínicí techniku')
        ->assertSee('Nezávazná poptávka');
});

it('renders the privacy page', function () {
    $this->get('/ochrana-osobnich-udaju')
        ->assertOk()
        ->assertSee('Ochrana osobních údajů');
});

it('renders the inquiry form for guests', function () {
    $this->get('/poptavka')
        ->assertOk()
        ->assertSee('Napište nám, co potřebujete')
        ->assertSee('Způsob zaměření');
});

it('renders the thanks page', function () {
    $this->get('/poptavka/dekujeme')
        ->assertOk()
        ->assertSee('Děkujeme za poptávku');
});

it('serves the sitemap as xml', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('web.inquiry.create'), false)
        ->assertSee(route('web.kalkulacka'), false)
        ->assertSee(route('web.kalkulacka.sivani'), false)
        ->assertSee(route('web.kalkulacka.stropy'), false)
        ->assertSee(route('web.stinici-technika'), false)
        ->assertDontSee('/kalkulacka/stinici-technika', false);
});

it('redirects guests from the internal dashboard to login', function () {
    $this->get('/prehled')->assertRedirect(route('login'));
});

it('redirects authenticated users from the internal dashboard by role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/prehled')
        ->assertRedirect(route('admin.orders.index'));

    $this->actingAs(User::factory()->create())
        ->get('/prehled')
        ->assertRedirect(route('orders.welcome'));
});
