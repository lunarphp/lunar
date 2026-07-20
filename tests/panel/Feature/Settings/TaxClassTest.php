<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\TaxClass;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the tax classes index renders with the real tax class list', function () {
    TaxClass::factory()->create(['name' => 'Standard', 'default' => true]);
    TaxClass::factory()->create(['name' => 'Zero rated', 'default' => false]);

    $this->get(route('panel.settings.tax-classes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/tax-classes/Index')
            ->has('taxClasses.data', 2)
            ->where('taxClasses.data.0.name', 'Standard')
            ->where('taxClasses.data.0.default', true)
            ->whereType('taxClasses.data.0.default', 'boolean')
            ->where('taxClasses.data.1.name', 'Zero rated')
            ->has('urls.store')
        );
});

test('tax classes carry first-party row actions, with delete omitted for the default class', function () {
    TaxClass::factory()->create(['name' => 'Standard', 'default' => true]);
    TaxClass::factory()->create(['name' => 'Zero rated', 'default' => false]);

    $this->get(route('panel.settings.tax-classes.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('taxClasses.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('taxClasses.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('a tax class can be created', function () {
    $this->post(route('panel.settings.tax-classes.store'), [
        'name' => 'Reduced rate',
    ])->assertRedirect(route('panel.settings.tax-classes.index'))
        ->assertSessionHas('success');

    $taxClass = TaxClass::where('name', 'Reduced rate')->first();

    expect($taxClass)->not->toBeNull();
    expect($taxClass->default)->toBeFalse();
});

test('creating a second tax class as default un-defaults the first', function () {
    $first = TaxClass::factory()->create(['name' => 'Standard', 'default' => true]);

    $this->post(route('panel.settings.tax-classes.store'), [
        'name' => 'Reduced rate',
        'default' => true,
    ])->assertRedirect(route('panel.settings.tax-classes.index'));

    expect($first->fresh()->default)->toBeFalse();
    expect(TaxClass::where('name', 'Reduced rate')->first()->default)->toBeTrue();
    expect(TaxClass::where('default', true)->count())->toBe(1);
});

test('a tax class can be updated', function () {
    $taxClass = TaxClass::factory()->create(['name' => 'Standard', 'default' => false]);

    $this->put(route('panel.settings.tax-classes.update', $taxClass), [
        'name' => 'Standard rate',
    ])->assertRedirect(route('panel.settings.tax-classes.index'))
        ->assertSessionHas('success');

    expect($taxClass->fresh()->name)->toBe('Standard rate');
});

test('unsetting default on the default tax class is rejected with a flash error', function () {
    $taxClass = TaxClass::factory()->create(['name' => 'Standard', 'default' => true]);

    $this->from(route('panel.settings.tax-classes.edit', $taxClass))
        ->put(route('panel.settings.tax-classes.update', $taxClass), [
            'name' => 'Standard',
            'default' => false,
        ])->assertRedirect(route('panel.settings.tax-classes.edit', $taxClass))
        ->assertSessionHas('error', __('panel::tax_classes.default_unset_blocked'));

    expect($taxClass->fresh()->default)->toBeTrue();
});

test('the default tax class cannot be deleted and shows a flash error', function () {
    $taxClass = TaxClass::factory()->create(['default' => true]);

    $this->from(route('panel.settings.tax-classes.edit', $taxClass))
        ->delete(route('panel.settings.tax-classes.destroy', $taxClass))
        ->assertRedirect(route('panel.settings.tax-classes.edit', $taxClass))
        ->assertSessionHas('error', __('panel::tax_classes.delete_blocked_default'));

    expect(TaxClass::find($taxClass->id))->not->toBeNull();
});

test('a tax class with no variants can be deleted', function () {
    $taxClass = TaxClass::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.tax-classes.destroy', $taxClass))
        ->assertRedirect(route('panel.settings.tax-classes.index'))
        ->assertSessionHas('success');

    expect(TaxClass::find($taxClass->id))->toBeNull();
});

test('a tax class with variants cannot be deleted and shows a flash error', function () {
    $taxClass = TaxClass::factory()->create(['default' => false]);
    // Variant creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
    ProductVariant::factory()->create(['tax_class_id' => $taxClass->id]);

    $this->from(route('panel.settings.tax-classes.edit', $taxClass))
        ->delete(route('panel.settings.tax-classes.destroy', $taxClass))
        ->assertRedirect(route('panel.settings.tax-classes.edit', $taxClass))
        ->assertSessionHas('error', __('panel::tax_classes.delete_blocked'));

    expect(TaxClass::find($taxClass->id))->not->toBeNull();
});
