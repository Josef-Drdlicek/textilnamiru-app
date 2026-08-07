<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Přihlašovací jméno');
});

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create(['username' => 'lucien']);

    $this->post('/login', ['username' => 'lucien', 'password' => 'password'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['username' => 'lucien']);

    $this->from('/login')
        ->post('/login', ['username' => 'lucien', 'password' => 'wrong'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('rejects a deactivated account with the same error as a wrong password', function () {
    User::factory()->inactive()->create(['username' => 'lucien']);

    $this->post('/login', ['username' => 'lucien', 'password' => 'password'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('logs out a user whose account was deactivated mid-session', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $user->update(['is_active' => false]);

    $this->get('/prehled')->assertRedirect('/login');
    $this->assertGuest();
});

it('logs out via POST', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

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
        $this->post('/login', ['username' => 'lucien', 'password' => 'wrong']);
    }

    $this->post('/login', ['username' => 'lucien', 'password' => 'password'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});
