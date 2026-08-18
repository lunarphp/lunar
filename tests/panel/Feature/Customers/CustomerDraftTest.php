<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function actingStaff(): Staff
{
    $staff = Staff::factory()->create(['admin' => true]);

    test()->actingAs($staff, 'staff');

    return $staff;
}

it('autosaves a draft and returns its state', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Changed')
        ->assertJsonPath('updated_at', fn (?string $value) => $value !== null);

    expect(EditDraft::count())->toBe(1);
});

it('deletes the draft when an empty diff is autosaved', function () {
    actingStaff();

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    $this->patchJson(route('panel.customers.draft.update', $customer), ['data' => []])
        ->assertOk()
        ->assertJsonPath('updated_at', null);

    expect(EditDraft::count())->toBe(0);
});

it('rejects autosaves with unknown field keys', function () {
    actingStaff();

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['admin_notes' => 'not draftable'],
    ])->assertUnprocessable();
});

it('hydrates the edit page with the current staff member\'s draft', function () {
    actingStaff();

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page
            ->where('draft.data.first_name', 'Changed')
            ->whereNot('draft.updated_at', null)
            ->where('urls.draft', route('panel.customers.draft.update', $customer))
            ->where('urls.draftCommit', route('panel.customers.draft.commit', $customer))
        );
});

it('keeps drafts invisible to other staff', function () {
    actingStaff();

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page->where('draft', null));
});

it('discards a draft', function () {
    actingStaff();

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    $this->deleteJson(route('panel.customers.draft.destroy', $customer))
        ->assertNoContent();

    expect(EditDraft::count())->toBe(0);
});

it('commits a draft, applies the changes and flashes success', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    $this->postJson(route('panel.customers.draft.commit', $customer))
        ->assertOk()
        ->assertJsonPath('committed', true)
        ->assertSessionHas('success');

    expect($customer->refresh()->first_name)->toBe('Changed')
        ->and(EditDraft::count())->toBe(0);
});

it('merges the request diff into the draft at commit time', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original', 'company_name' => 'Acme']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertOk();

    // The un-debounced final keystrokes ride along with the commit.
    $this->postJson(route('panel.customers.draft.commit', $customer), [
        'data' => ['company_name' => 'Globex'],
    ])->assertOk();

    expect($customer->refresh()->first_name)->toBe('Changed')
        ->and($customer->company_name)->toBe('Globex');
});

it('returns 409 with per-field detail when a drafted field conflicts', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original', 'company_name' => 'Acme']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Mine', 'company_name' => 'Globex'],
    ])->assertOk();

    $customer->update(['first_name' => 'Theirs']);

    $this->postJson(route('panel.customers.draft.commit', $customer))
        ->assertStatus(409)
        ->assertJsonCount(1, 'conflicts')
        ->assertJsonPath('conflicts.0.key', 'first_name')
        ->assertJsonPath('conflicts.0.label', __('panel::customers.field_first_name'))
        ->assertJsonPath('conflicts.0.mine', 'Mine')
        ->assertJsonPath('conflicts.0.base', 'Original')
        ->assertJsonPath('conflicts.0.theirs', 'Theirs');

    // Atomic: nothing applied, the draft survives for resolution.
    expect($customer->refresh()->company_name)->toBe('Acme')
        ->and(EditDraft::count())->toBe(1);
});

it('commits resolved conflicts sent with a rebase payload', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Mine'],
    ])->assertOk();

    $customer->update(['first_name' => 'Theirs']);

    $this->postJson(route('panel.customers.draft.commit', $customer))->assertStatus(409);

    $this->postJson(route('panel.customers.draft.commit', $customer), [
        'data' => ['first_name' => 'Mine'],
        'rebase' => ['first_name' => 'Theirs'],
    ])->assertOk();

    expect($customer->refresh()->first_name)->toBe('Mine');
});

it('returns 422 when the merged payload fails validation', function () {
    actingStaff();

    $customer = Customer::factory()->create(['first_name' => 'Original']);

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => ''],
    ])->assertOk();

    $this->postJson(route('panel.customers.draft.commit', $customer))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('first_name');

    expect($customer->refresh()->first_name)->toBe('Original');
});

it('requires the manage-customers permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Changed'],
    ])->assertForbidden();

    $this->postJson(route('panel.customers.draft.commit', $customer))->assertForbidden();

    $this->deleteJson(route('panel.customers.draft.destroy', $customer))->assertForbidden();
});
