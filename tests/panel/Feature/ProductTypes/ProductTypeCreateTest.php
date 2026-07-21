<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('renders the create form', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.product-types.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('product-types/Create')
            ->has('urls.store')
        );
});

it('creates a product type and redirects to its edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $response = $this->post(route('panel.product-types.store'), [
        'name' => 'Stationery',
        'status' => 'draft',
    ]);

    $productType = ProductType::sole();

    $response->assertRedirect(route('panel.product-types.edit', $productType))
        ->assertSessionHas('success', 'Product type created.');

    expect($productType)
        ->name->toBe('Stationery')
        ->handle->toBe('stationery')
        ->and($productType->status->getValue())->toBe('draft');
});

it('defaults new product types to active', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.product-types.store'), ['name' => 'Stationery']);

    expect(ProductType::sole()->status->getValue())->toBe('active');
});

it('validates the payload', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.product-types.store'), ['name' => ''])
        ->assertSessionHasErrors('name');

    $this->post(route('panel.product-types.store'), ['name' => 'Ok', 'status' => 'archived'])
        ->assertSessionHasErrors('status');
});
