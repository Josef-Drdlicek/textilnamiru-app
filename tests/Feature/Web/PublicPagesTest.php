<?php

use App\Models\User;

it('renders the public homepage for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Interiérový textil šitý')
        ->assertSee('Náš sortiment')
        ->assertSee('Kolik bude stát ušití?')
        ->assertSee('Rodinná tradice od roku 1992')
        ->assertSee('Vypínaný stropní podhled v interiéru')
        ->assertSee('Stínící technika v interiéru')
        ->assertSee('Vypínaný stropní podhled')
        ->assertSee('Nová technologie stropů')
        ->assertSee('Stínění pro každou situaci');
});

it('links the header B2B button to the b2b page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('>B2B</a>', false);
});

it('shows catalog prices from the live price list', function () {
    $this->get('/sortiment')
        ->assertOk()
        ->assertSee('za ušití vč. DPH, bez látky')
        ->assertSee('Na poptávku');
});

it('renders the catalog even when the price list is empty', function () {
    \DB::table('pricing_variables')->delete();
    \DB::table('ribbons')->delete();
    \Cache::flush();

    $this->get('/sortiment')
        ->assertOk()
        ->assertSee('Na poptávku');
});

it('does not repeat full catalog, calculator or gallery on the homepage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Zobrazit více')
        ->assertDontSee('Spočítat cenu')
        ->assertDontSee('Hotová šířka')
        ->assertDontSee('Hotel Voroněž, Brno');
});

it('renders the homepage for authenticated users without redirecting', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('Náš sortiment');
});

it('renders the calculator hub with three tiles', function () {
    $this->get('/kalkulacka')
        ->assertOk()
        ->assertSee('Kalkulačka')
        ->assertSee('Zakázkové šití')
        ->assertSee('Vypínané stropy')
        ->assertSee('Stínící technika')
        ->assertSee('DOPLNIT OBRÁZEK')
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
        ->assertSee('Napínaný strop v interiéru – fotka 1 z 5')
        ->assertSee('images/vypinany-podhled/01.png', false)
        ->assertSee('Předchozí obrázek')
        ->assertSee('Další obrázek')
        ->assertSee('Co jsou napínané stropy?')
        ->assertSee('Hlavní výhody napínaných stropů')
        ->assertSee('Varianty provedení')
        ->assertSee('Kde lze napínané stropy využít?');
});

it('renders the shading-technology page', function () {
    $this->get('/stinici-technika')
        ->assertOk()
        ->assertSee('Stínící technika')
        ->assertSee('DOPLNIT OBSAH');
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

it('renders the catalog page', function () {
    $this->get('/sortiment')
        ->assertOk()
        ->assertSee('Náš sortiment')
        ->assertSee('Záclony')
        ->assertSee('Římské rolety');
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
        ->assertSee(route('web.kalkulacka.stinici'), false);
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
