<?php

use App\Models\User;

it('blocks non-admins from admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.orders.index'))
        ->assertForbidden();
});

it('allows admins into admin routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk();
});

it('redirects guests to login', function () {
    $this->get('/')->assertRedirect('/login');
});
