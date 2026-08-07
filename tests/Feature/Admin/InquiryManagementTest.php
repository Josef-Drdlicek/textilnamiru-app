<?php

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\User;

it('groups inquiries by status on the index', function () {
    Inquiry::factory()->create(['name' => 'Nová Zákaznice']);
    Inquiry::factory()->contacted()->create(['name' => 'Kontaktovaný Hotel']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.inquiries.index'))
        ->assertOk()
        ->assertSee('Nové poptávky')
        ->assertSee('Kontaktované')
        ->assertSee('Vyřízené')
        ->assertSee('Zamítnuté')
        ->assertSee('Nová Zákaznice')
        ->assertSee('Kontaktovaný Hotel');
});

it('shows the inquiry detail with the configuration snapshot', function () {
    $inquiry = Inquiry::factory()->withConfiguration()->create(['name' => 'Jana Veselá']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.inquiries.show', $inquiry))
        ->assertOk()
        ->assertSee('Jana Veselá')
        ->assertSee($inquiry->phone)
        ->assertSee('Konfigurace z kalkulačky')
        ->assertSee('Výplň');
});

it('shows the new-inquiries badge in the admin navigation', function () {
    Inquiry::factory()->count(2)->create();
    Inquiry::factory()->contacted()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.inquiries.index'))
        ->assertSee('Poptávky');

    expect(Inquiry::query()->new()->count())->toBe(2);
});

it('updates the inquiry status without transition constraints', function () {
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.inquiries.status', $inquiry), ['status' => 'rejected'])
        ->assertRedirect();

    expect($inquiry->refresh()->status)->toBe(InquiryStatus::Rejected);
});

it('rejects an unknown status value', function () {
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.inquiries.status', $inquiry), ['status' => 'vymysleny'])
        ->assertSessionHasErrors('status');

    expect($inquiry->refresh()->status)->toBe(InquiryStatus::New);
});

it('hard deletes an inquiry', function () {
    $inquiry = Inquiry::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.inquiries.destroy', $inquiry))
        ->assertRedirect(route('admin.inquiries.index'));

    expect(Inquiry::count())->toBe(0);
});

it('blocks non-admins from all inquiry routes', function () {
    $inquiry = Inquiry::factory()->create();
    $designer = User::factory()->create();

    $this->actingAs($designer)->get(route('admin.inquiries.index'))->assertForbidden();
    $this->actingAs($designer)->get(route('admin.inquiries.show', $inquiry))->assertForbidden();
    $this->actingAs($designer)->post(route('admin.inquiries.status', $inquiry), ['status' => 'resolved'])->assertForbidden();
    $this->actingAs($designer)->delete(route('admin.inquiries.destroy', $inquiry))->assertForbidden();
});

it('redirects guests from inquiry administration to login', function () {
    $this->get(route('admin.inquiries.index'))->assertRedirect('/login');
});
