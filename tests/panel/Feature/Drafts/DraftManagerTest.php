<?php

use Illuminate\Validation\ValidationException;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Models\EditDraft;
use Lunar\Panel\Sections\Sales\CustomerDraftResource;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function draftManager(): DraftManager
{
    return app(DraftManager::class);
}

function customerDraftResource(): CustomerDraftResource
{
    return app(CustomerDraftResource::class);
}

it('captures a base snapshot when a field first enters the draft', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Changed']);

    expect($draft->data)->toBe(['first_name' => 'Changed'])
        ->and($draft->base_snapshot)->toBe(['first_name' => 'Original']);
});

it('keeps the base snapshot fixed across further edits to the same field', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'First edit']);

    // The record moves on underneath; the snapshot must not follow it.
    $customer->update(['first_name' => 'Moved']);

    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Second edit']);

    expect($draft->data)->toBe(['first_name' => 'Second edit'])
        ->and($draft->base_snapshot)->toBe(['first_name' => 'Original']);
});

it('replaces data wholesale on merge, dropping snapshots for reverted fields', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original', 'company_name' => 'Acme']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, [
        'first_name' => 'Changed',
        'company_name' => 'Globex',
    ]);

    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, ['company_name' => 'Globex']);

    expect($draft->data)->toBe(['company_name' => 'Globex'])
        ->and($draft->base_snapshot)->toBe(['company_name' => 'Acme']);
});

it('deletes the draft when the diff shrinks to empty', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create();

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Changed']);

    expect(EditDraft::count())->toBe(1);

    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, []);

    expect($draft)->toBeNull()
        ->and(EditDraft::count())->toBe(0);
});

it('rejects field keys outside the resource definition', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create();

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['admin_notes' => 'sneaky']);
})->throws(ValidationException::class);

it('normalises empty strings to null for nullable text fields', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['company_name' => null]);

    // '' from the form equals the stored null, so nothing is dirty.
    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, ['company_name' => '']);

    expect($draft->data)->toBe(['company_name' => null]);

    $result = draftManager()->commit(customerDraftResource(), $customer, $staff, []);

    expect($result->committed)->toBeTrue();
});

it('normalises customer group ids to a sorted unique set', function () {
    $staff = Staff::factory()->create();
    $groups = CustomerGroup::factory()->count(2)->create();
    [$low, $high] = $groups->sortBy('id')->values();

    $customer = Customer::factory()->create();
    $customer->customerGroups()->sync([$low->id, $high->id]);

    // Same membership in a different order is not a change.
    $draft = draftManager()->merge(customerDraftResource(), $customer, $staff, [
        'customer_group_ids' => [(string) $high->id, $low->id, $low->id],
    ]);

    expect($draft->data['customer_group_ids'])->toBe([$low->id, $high->id]);

    $result = draftManager()->commit(customerDraftResource(), $customer, $staff, []);

    expect($result->committed)->toBeTrue()
        ->and($customer->refresh()->customerGroups)->toHaveCount(2);
});

it('commits cleanly when no other write touched the drafted fields', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Changed']);

    $result = draftManager()->commit(customerDraftResource(), $customer, $staff, []);

    expect($result->committed)->toBeTrue()
        ->and($result->conflicts)->toBe([])
        ->and($customer->refresh()->first_name)->toBe('Changed')
        ->and(EditDraft::count())->toBe(0);
});

it('lets two staff editing disjoint fields both commit without conflict', function () {
    $staffA = Staff::factory()->create();
    $staffB = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original', 'company_name' => 'Acme']);

    draftManager()->merge(customerDraftResource(), $customer, $staffA, ['first_name' => 'Alice']);
    draftManager()->merge(customerDraftResource(), $customer, $staffB, ['company_name' => 'Globex']);

    $resultA = draftManager()->commit(customerDraftResource(), $customer, $staffA, []);
    $resultB = draftManager()->commit(customerDraftResource(), $customer->refresh(), $staffB, []);

    expect($resultA->committed)->toBeTrue()
        ->and($resultB->committed)->toBeTrue()
        ->and($customer->refresh()->first_name)->toBe('Alice')
        ->and($customer->company_name)->toBe('Globex');
});

it('flags a conflict when another write changed a drafted field, applying nothing', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original', 'company_name' => 'Acme']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, [
        'first_name' => 'Mine',
        'company_name' => 'Globex',
    ]);

    // A concurrent editor wins the race on first_name.
    $customer->update(['first_name' => 'Theirs']);

    $result = draftManager()->commit(customerDraftResource(), $customer, $staff, []);

    expect($result->committed)->toBeFalse()
        ->and($result->conflicts)->toHaveCount(1)
        ->and($result->conflicts[0])->toMatchArray([
            'key' => 'first_name',
            'mine' => 'Mine',
            'base' => 'Original',
            'theirs' => 'Theirs',
        ])
        // Atomic: the clean company_name change must not have applied either.
        ->and($customer->refresh()->company_name)->toBe('Acme')
        ->and(EditDraft::count())->toBe(1);
});

it('commits after a rebase pinning the resolved field to the value the user saw', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Mine']);
    $customer->update(['first_name' => 'Theirs']);

    expect(draftManager()->commit(customerDraftResource(), $customer, $staff, [])->committed)->toBeFalse();

    $result = draftManager()->commit(
        customerDraftResource(),
        $customer,
        $staff,
        ['first_name' => 'Mine'],
        rebase: ['first_name' => 'Theirs'],
    );

    expect($result->committed)->toBeTrue()
        ->and($customer->refresh()->first_name)->toBe('Mine');
});

it('re-conflicts when the record moves again after the user resolved', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Mine']);
    $customer->update(['first_name' => 'Theirs']);

    expect(draftManager()->commit(customerDraftResource(), $customer, $staff, [])->committed)->toBeFalse();

    // The user resolved against 'Theirs', but a third write lands first.
    $customer->update(['first_name' => 'Newer still']);

    $result = draftManager()->commit(
        customerDraftResource(),
        $customer,
        $staff,
        ['first_name' => 'Mine'],
        rebase: ['first_name' => 'Theirs'],
    );

    expect($result->committed)->toBeFalse()
        ->and($result->conflicts[0]['theirs'])->toBe('Newer still')
        ->and($customer->refresh()->first_name)->toBe('Newer still');
});

it('validates the full payload before conflict detection', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create(['first_name' => 'Original']);

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => '']);

    draftManager()->commit(customerDraftResource(), $customer, $staff, []);
})->throws(ValidationException::class);

it('treats a commit with no draft and no diff as a no-op success', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create();

    $result = draftManager()->commit(customerDraftResource(), $customer, $staff, []);

    expect($result->committed)->toBeTrue();
});

it('keeps one draft per staff member per record', function () {
    $staffA = Staff::factory()->create();
    $staffB = Staff::factory()->create();
    $customer = Customer::factory()->create();

    draftManager()->merge(customerDraftResource(), $customer, $staffA, ['first_name' => 'A']);
    draftManager()->merge(customerDraftResource(), $customer, $staffA, ['first_name' => 'A2']);
    draftManager()->merge(customerDraftResource(), $customer, $staffB, ['first_name' => 'B']);

    expect(EditDraft::count())->toBe(2)
        ->and(draftManager()->find($customer, $staffA)->data['first_name'])->toBe('A2')
        ->and(draftManager()->find($customer, $staffB)->data['first_name'])->toBe('B');
});

it('removes drafts when their record is deleted', function () {
    $staff = Staff::factory()->create();
    $customer = Customer::factory()->create();

    draftManager()->merge(customerDraftResource(), $customer, $staff, ['first_name' => 'Changed']);

    $customer->delete();

    expect(EditDraft::count())->toBe(0);
});

it('prunes drafts untouched beyond the configured ttl', function () {
    EditDraft::factory()->create();

    $this->travel(8)->days();

    $fresh = EditDraft::factory()->create();

    $this->artisan('model:prune', ['--model' => [EditDraft::class]]);

    expect(EditDraft::count())->toBe(1)
        ->and(EditDraft::first()->id)->toBe($fresh->id);
});
