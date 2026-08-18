<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Brand creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
});

it('renders the create form', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.brands.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('brands/Create')
            ->has('urls.store')
        );
});

it('creates a brand and redirects to its edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $response = $this->post(route('panel.brands.store'), [
        'name' => 'Stark Industries',
        'status' => 'draft',
    ]);

    $brand = Brand::sole();

    $response->assertRedirect(route('panel.brands.edit', $brand))
        ->assertSessionHas('success', 'Brand created.');

    expect($brand)
        ->name->toBe('Stark Industries')
        ->handle->toBe('stark-industries')
        ->and($brand->status->getValue())->toBe('draft');
});

it('defaults new brands to active', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.brands.store'), ['name' => 'Stark Industries']);

    expect(Brand::sole()->status->getValue())->toBe('active');
});

it('validates the payload', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.brands.store'), ['name' => ''])
        ->assertSessionHasErrors('name');

    $this->post(route('panel.brands.store'), ['name' => 'Ok', 'status' => 'archived'])
        ->assertSessionHasErrors('status');
});
