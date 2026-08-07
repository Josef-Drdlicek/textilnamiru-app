<?php

use App\Models\User;

it('renders the public homepage for guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Interiérový textil šitý')
        ->assertSee('Náš sortiment')
        ->assertSee('Kolik bude stát ušití?')
        ->assertSee('Rodinná tradice od roku 1992');
});

it('shows catalog prices from the live price list', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('za ušití vč. DPH, bez látky')
        ->assertSee('Na poptávku');
});

it('renders the homepage for authenticated users without redirecting', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk()
        ->assertSee('Náš sortiment');
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
        ->assertSee(route('web.inquiry.create'), false);
});

it('redirects guests from the internal dashboard to login', function () {
    $this->get('/prehled')->assertRedirect('/login');
});

it('redirects authenticated users from the internal dashboard by role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/prehled')
        ->assertRedirect(route('admin.orders.index'));

    $this->actingAs(User::factory()->create())
        ->get('/prehled')
        ->assertRedirect(route('orders.welcome'));
});
