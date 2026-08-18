<?php

use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\StaffPreference;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('persists the staff member\'s layout and range', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($staff, 'staff');

    $this->put(route('panel.dashboard.preferences.update'), [
        'range' => '7d',
        'widgets' => [
            ['key' => 'revenue-chart', 'visible' => true],
            ['key' => 'kpis', 'visible' => false],
        ],
    ])->assertNoContent();

    expect(StaffPreference::valueFor($staff, 'dashboard'))->toBe([
        'range' => '7d',
        'widgets' => [
            ['key' => 'revenue-chart', 'visible' => true],
            ['key' => 'kpis', 'visible' => false],
        ],
    ]);
});

it('drops unknown and duplicate widget keys rather than rejecting', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($staff, 'staff');

    $this->put(route('panel.dashboard.preferences.update'), [
        'range' => '30d',
        'widgets' => [
            ['key' => 'kpis', 'visible' => true],
            ['key' => 'kpis', 'visible' => false],
            ['key' => 'not-a-widget', 'visible' => true],
        ],
    ])->assertNoContent();

    expect(StaffPreference::valueFor($staff, 'dashboard')['widgets'])->toBe([
        ['key' => 'kpis', 'visible' => true],
    ]);
});

it('rejects an invalid range and malformed widget entries', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->putJson(route('panel.dashboard.preferences.update'), [
        'range' => 'fortnight',
        'widgets' => [['key' => 'kpis', 'visible' => true]],
    ])->assertJsonValidationErrors(['range']);

    $this->putJson(route('panel.dashboard.preferences.update'), [
        'range' => '30d',
        'widgets' => [['visible' => true]],
    ])->assertJsonValidationErrors(['widgets.0.key']);
});

it('drops widget keys the staff member cannot see', function () {
    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('sales:manage-orders');
    $this->actingAs($staff, 'staff');

    $this->put(route('panel.dashboard.preferences.update'), [
        'range' => '30d',
        'widgets' => [
            ['key' => 'kpis', 'visible' => true],
            // Needs catalog:manage-products, which this staff member lacks.
            ['key' => 'low-stock', 'visible' => true],
        ],
    ])->assertNoContent();

    expect(StaffPreference::valueFor($staff, 'dashboard')['widgets'])->toBe([
        ['key' => 'kpis', 'visible' => true],
    ]);
});

it('resets the layout to defaults', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    StaffPreference::factory()->for($staff)->create();

    $this->actingAs($staff, 'staff');

    $this->delete(route('panel.dashboard.preferences.destroy'))->assertNoContent();

    expect(StaffPreference::valueFor($staff, 'dashboard'))->toBeNull();
});

it('keeps preferences per staff member', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $other = Staff::factory()->create(['admin' => true]);

    StaffPreference::factory()->for($other)->create([
        'value' => ['range' => '90d', 'widgets' => []],
    ]);

    $this->actingAs($staff, 'staff');

    $this->put(route('panel.dashboard.preferences.update'), [
        'range' => '7d',
        'widgets' => [],
    ])->assertNoContent();

    expect(StaffPreference::valueFor($other, 'dashboard')['range'])->toBe('90d');
    expect(StaffPreference::valueFor($staff, 'dashboard')['range'])->toBe('7d');
});

it('requires authentication', function () {
    $this->put(route('panel.dashboard.preferences.update'), [
        'range' => '30d',
        'widgets' => [],
    ])->assertRedirect(route('panel.login'));
});
