<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('shows the profile form with a read-only username', function () {
    $user = User::factory()->create(['username' => 'lucien']);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('lucien');
});

it('updates name, phone and address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Lucie Nováková',
            'phone' => '+420 777 111 222',
            'address' => "Lucie Nováková\nKvětná 12\n602 00 Brno",
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh())
        ->name->toBe('Lucie Nováková')
        ->phone->toBe('+420 777 111 222')
        ->address->toContain('Květná 12');
});

it('changes the password only when provided', function () {
    $user = User::factory()->create();
    $originalHash = $user->password;

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'password' => '',
    ]);

    expect($user->refresh()->password)->toBe($originalHash);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'password' => 'noveTajneHeslo123',
        'password_confirmation' => 'noveTajneHeslo123',
    ]);

    expect(Hash::check('noveTajneHeslo123', $user->refresh()->password))->toBeTrue();
});

it('does not allow changing the username via profile', function () {
    $user = User::factory()->create(['username' => 'lucien']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'username' => 'hacker',
    ]);

    expect($user->refresh()->username)->toBe('lucien');
});
