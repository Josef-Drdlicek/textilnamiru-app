<?php

use App\Models\User;

it('redirects guests from the b2b app to the b2b login', function () {
    $this->get('/b2b/aplikace')
        ->assertRedirect(route('web.b2b'));
});

it('serves the order app only to authenticated users and asks robots not to index it', function () {
    $user = User::factory()->create(['username' => 'lucien', 'name' => 'Lucie']);

    $this->actingAs($user)
        ->get(route('web.b2b.aplikace'))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Textil na míru', false)
        ->assertSee('tnm_session', false)
        ->assertSee('__TNM_CSRF', false)
        ->assertSee('lucien', false)
        ->assertSee('"admin":false', false);
});

it('maps laravel admins to the js admin flag', function () {
    $admin = User::factory()->admin()->create(['username' => 'dilna']);

    $html = $this->actingAs($admin)
        ->get(route('web.b2b.aplikace'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('"admin":true');
});

it('redirects already logged-in users from /b2b to the app', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/b2b')
        ->assertRedirect(route('web.b2b.aplikace'));
});

it('keeps the header B2B button pointing at /b2b', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('web.b2b').'"', false)
        ->assertSee('>B2B</a>', false)
        ->assertDontSee('href="'.route('web.b2b.aplikace').'"', false);
});
