<?php

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * Smoke coverage: the controllers delegate to the same URL actions the
 * brand endpoints proved out (spec 0052); these tests pin the collection
 * routes, bindings and per-language uniqueness against the collection morph.
 */
beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    // Disable the HasUrls auto-generator so each test owns its URL rows.
    config(['lunar.urls.generator' => null]);

    $this->language = Language::factory()->create(['default' => true, 'code' => 'en']);
    $this->collection = Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
    ]);
});

it('adds a url, forcing the first to be the default', function () {
    $this->post(route('panel.collections.urls.store', $this->collection), [
        'language_id' => $this->language->id,
        'slug' => 'outerwear',
        'default' => false,
    ])->assertRedirect()->assertSessionHas('success');

    $url = $this->collection->urls()->sole();

    expect($url->slug)->toBe('outerwear')
        ->and($url->default)->toBeTrue();
});

it('enforces slug uniqueness per language across collections', function () {
    $other = Collection::factory()->create(['collection_group_id' => CollectionGroup::factory()]);
    $other->urls()->create(['language_id' => $this->language->id, 'slug' => 'taken', 'default' => true]);

    $this->post(route('panel.collections.urls.store', $this->collection), [
        'language_id' => $this->language->id,
        'slug' => 'taken',
    ])->assertSessionHasErrors('slug');
});

it('re-points the default when the default url is deleted', function () {
    $default = $this->collection->urls()->create(['language_id' => $this->language->id, 'slug' => 'one', 'default' => true]);
    $other = $this->collection->urls()->create(['language_id' => $this->language->id, 'slug' => 'two', 'default' => false]);

    $this->delete(route('panel.collections.urls.destroy', [$this->collection, $default]))
        ->assertRedirect()->assertSessionHas('success');

    expect($other->refresh()->default)->toBeTrue();
});

it('scopes urls to the parent collection', function () {
    $other = Collection::factory()->create(['collection_group_id' => CollectionGroup::factory()]);
    $url = $other->urls()->create(['language_id' => $this->language->id, 'slug' => 'elsewhere', 'default' => true]);

    $this->delete(route('panel.collections.urls.destroy', [$this->collection, $url]))
        ->assertNotFound();
});

it('gates url endpoints behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.collections.urls.store', $this->collection), [
        'language_id' => $this->language->id,
        'slug' => 'nope',
    ])->assertForbidden();
});
