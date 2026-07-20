<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Tag;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the tags index renders with the real tag list and usage counts', function () {
    $tag = Tag::factory()->create(['value' => 'SALE']);
    Tag::factory()->create(['value' => 'NEW']);

    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
    $product = Product::factory()->create();
    $product->tags()->attach($tag);

    $this->get(route('panel.settings.tags.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/tags/Index')
            ->has('tags.data', 2)
            ->where('tags.data.0.value', 'NEW')
            ->where('tags.data.0.usage_count', 0)
            ->where('tags.data.1.value', 'SALE')
            ->where('tags.data.1.usage_count', 1)
            ->has('urls.store')
        );
});

test('tags carry a first-party delete row action', function () {
    Tag::factory()->create(['value' => 'SALE']);

    $this->get(route('panel.settings.tags.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['delete'])
            ->where('tags.data.0._actions', fn ($actions) => isset($actions['delete']))
        );
});

test('a tag can be created and is upper-cased', function () {
    $this->from(route('panel.settings.tags.index'))
        ->post(route('panel.settings.tags.store'), ['value' => 'clearance'])
        ->assertRedirect(route('panel.settings.tags.index'))
        ->assertSessionHas('success');

    expect(Tag::where('value', 'CLEARANCE')->exists())->toBeTrue();
});

test('a duplicate tag value is rejected', function () {
    Tag::factory()->create(['value' => 'SALE']);

    $this->post(route('panel.settings.tags.store'), ['value' => 'sale'])
        ->assertSessionHasErrors('value');

    expect(Tag::count())->toBe(1);
});

test('a tag can be renamed', function () {
    $tag = Tag::factory()->create(['value' => 'SALE']);

    $this->from(route('panel.settings.tags.index'))
        ->put(route('panel.settings.tags.update', $tag), ['value' => 'outlet'])
        ->assertRedirect(route('panel.settings.tags.index'))
        ->assertSessionHas('success');

    expect($tag->fresh()->value)->toBe('OUTLET');
});

test('a tag can be deleted and tagged records are untagged', function () {
    $tag = Tag::factory()->create(['value' => 'SALE']);
    // Product creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
    $product = Product::factory()->create();
    $product->tags()->attach($tag);

    $this->from(route('panel.settings.tags.index'))
        ->delete(route('panel.settings.tags.destroy', $tag))
        ->assertRedirect(route('panel.settings.tags.index'))
        ->assertSessionHas('success');

    expect(Tag::find($tag->id))->toBeNull();
    expect($product->tags()->count())->toBe(0);
});
