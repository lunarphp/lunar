<?php

use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    // Disable the HasUrls auto-generator so each test owns its URL rows.
    config(['lunar.urls.generator' => null]);

    $this->language = Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->brand = Brand::factory()->create();
});

it('adds a url, forcing the first to be the default', function () {
    $this->post(route('panel.brands.urls.store', $this->brand), [
        'language_id' => $this->language->id,
        'slug' => 'stark',
        'default' => false,
    ])->assertRedirect()->assertSessionHas('success');

    $url = $this->brand->urls()->sole();

    expect($url->slug)->toBe('stark')
        ->and($url->default)->toBeTrue();
});

it('allows several slugs in the same language', function () {
    $this->brand->urls()->create(['language_id' => $this->language->id, 'slug' => 'stark', 'default' => true]);

    $this->post(route('panel.brands.urls.store', $this->brand), [
        'language_id' => $this->language->id,
        'slug' => 'stark-industries',
    ])->assertRedirect()->assertSessionDoesntHaveErrors();

    expect($this->brand->urls()->where('language_id', $this->language->id)->count())->toBe(2)
        ->and($this->brand->urls()->where('default', true)->sole()->slug)->toBe('stark');
});

it('validates the slug format', function () {
    $this->post(route('panel.brands.urls.store', $this->brand), [
        'language_id' => $this->language->id,
        'slug' => 'Not A Slug!',
    ])->assertSessionHasErrors('slug');
});

it('enforces slug uniqueness per language across brands', function () {
    $other = Brand::factory()->create();
    $other->urls()->create(['language_id' => $this->language->id, 'slug' => 'taken', 'default' => true]);

    $this->post(route('panel.brands.urls.store', $this->brand), [
        'language_id' => $this->language->id,
        'slug' => 'taken',
    ])->assertSessionHasErrors('slug');

    $german = Language::factory()->create(['code' => 'de']);

    $this->post(route('panel.brands.urls.store', $this->brand), [
        'language_id' => $german->id,
        'slug' => 'taken',
    ])->assertSessionDoesntHaveErrors('slug');
});

it('updates a slug and promotes a new default', function () {
    $default = $this->brand->urls()->create(['language_id' => $this->language->id, 'slug' => 'stark', 'default' => true]);

    $german = Language::factory()->create(['code' => 'de']);
    $other = $this->brand->urls()->create(['language_id' => $german->id, 'slug' => 'stark-de', 'default' => false]);

    $this->put(route('panel.brands.urls.update', [$this->brand, $other]), [
        'language_id' => $german->id,
        'slug' => 'stark-germany',
        'default' => true,
    ])->assertRedirect()->assertSessionHas('success');

    expect($other->refresh())
        ->slug->toBe('stark-germany')
        ->default->toBeTrue()
        ->and($default->refresh()->default)->toBeFalse();
});

it('re-points the default when the default url is deleted', function () {
    $default = $this->brand->urls()->create(['language_id' => $this->language->id, 'slug' => 'stark', 'default' => true]);

    $german = Language::factory()->create(['code' => 'de']);
    $other = $this->brand->urls()->create(['language_id' => $german->id, 'slug' => 'stark-de', 'default' => false]);

    $this->delete(route('panel.brands.urls.destroy', [$this->brand, $default]))
        ->assertRedirect()->assertSessionHas('success');

    expect($this->brand->urls()->count())->toBe(1)
        ->and($other->refresh()->default)->toBeTrue();
});

it('scopes url bindings to the brand', function () {
    $other = Brand::factory()->create();
    $foreign = $other->urls()->create(['language_id' => $this->language->id, 'slug' => 'foreign', 'default' => true]);

    $this->delete(route('panel.brands.urls.destroy', [$this->brand, $foreign]))
        ->assertNotFound();

    expect($other->urls()->count())->toBe(1);
});
