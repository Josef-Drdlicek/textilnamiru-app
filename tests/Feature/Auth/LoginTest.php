<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Přihlášení pro interiérová studia');
});

it('permanently redirects the legacy login url', function () {
    $this->get('/login')->assertRedirect(route('login'))->assertStatus(301);
});

it('sends an authenticated user from the login page to the overview', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

it('logs in from the public b2b form', function () {
    $user = User::factory()->create(['username' => 'lucien']);

    $this->from(route('login'))
        ->post(route('login.store'), ['username' => 'lucien', 'password' => 'password'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('returns validation errors back to the b2b form', function () {
    User::factory()->create(['username' => 'lucien']);

    $this->from(route('login'))
        ->post(route('login.store'), ['username' => 'lucien', 'password' => 'wrong'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('rejects invalid credentials', function () {
    User::factory()->create(['username' => 'lucien']);

    $this->from(route('login'))
        ->post(route('login.store'), ['username' => 'lucien', 'password' => 'wrong'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('rejects a deactivated account with the same error as a wrong password', function () {
    User::factory()->inactive()->create(['username' => 'lucien']);

    $this->post(route('login.store'), ['username' => 'lucien', 'password' => 'password'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('logs out a user whose account was deactivated mid-session', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $user->update(['is_active' => false]);

    $this->get('/prehled')->assertRedirect(route('login'));
    $this->assertGuest();
});

it('logs out via POST', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('redirects admins to the admin dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/prehled')
        ->assertRedirect(route('admin.orders.index'));
});

it('redirects designers to the order welcome page after login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/prehled')
        ->assertRedirect(route('orders.welcome'));
});

it('rate limits repeated failed login attempts', function () {
    User::factory()->create(['username' => 'lucien']);

    foreach (range(1, 5) as $i) {
        $this->post(route('login.store'), ['username' => 'lucien', 'password' => 'wrong']);
    }

    $this->post(route('login.store'), ['username' => 'lucien', 'password' => 'password'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});
