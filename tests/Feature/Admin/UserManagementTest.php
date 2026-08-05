<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('lists users with their order counts', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Lucie Návrhářka']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Lucie Návrhářka');
});

it('renders the create form with submittable field names', function () {
    // Regrese: x-form.input dříve nevykresloval atribut name → prohlížeč
    // pole neodeslal a validace hlásila „povinné" i u vyplněného formuláře.
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('name="name"', escape: false)
        ->assertSee('name="username"', escape: false)
        ->assertSee('name="password"', escape: false)
        ->assertSee('name="phone"', escape: false);
});

it('creates a user account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Lucie Návrhářka',
            'username' => 'lucie',
            'password' => 'tajneHeslo123',
            'phone' => '+420 777 000 111',
            'is_admin' => '0',
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::query()->where('username', 'lucie')->sole();

    expect($user)
        ->name->toBe('Lucie Návrhářka')
        ->is_admin->toBeFalse()
        ->is_active->toBeTrue()
        ->and(Hash::check('tajneHeslo123', $user->password))->toBeTrue();
});

it('enforces unique usernames', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['username' => 'lucie']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Duplicitní', 'username' => 'lucie', 'password' => 'tajneHeslo123',
        ])
        ->assertSessionHasErrors('username');
});

it('updates a user and keeps the password unless provided', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $user), [
            'name' => 'Nové Jméno',
            'username' => $user->username,
            'password' => '',
            'is_admin' => '0',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($user->refresh())
        ->name->toBe('Nové Jméno')
        ->password->toBe($originalHash);
});

it('deactivates and reactivates a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.toggle-active', $user));
    expect($user->refresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->patch(route('admin.users.toggle-active', $user));
    expect($user->refresh()->is_active)->toBeTrue();
});

it('prevents an admin from deactivating their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle-active', $admin))
        ->assertForbidden();

    expect($admin->refresh()->is_active)->toBeTrue();
});

it('blocks non-admins from user management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.users.store'), [])->assertForbidden();
});
