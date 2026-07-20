<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Core\Models\Url;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the languages index renders with the real language list', function () {
    Language::factory()->create(['code' => 'en', 'name' => 'English', 'default' => true]);
    Language::factory()->create(['code' => 'de', 'name' => 'German', 'default' => false]);

    $this->get(route('panel.settings.languages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/languages/Index')
            ->has('languages.data', 2)
            ->where('languages.data.0.code', 'de')
            ->where('languages.data.1.code', 'en')
            ->where('languages.data.1.default', true)
            ->whereType('languages.data.1.default', 'boolean')
            ->has('urls.store')
        );
});

test('languages carry first-party row actions, with delete omitted for the default language', function () {
    Language::factory()->create(['code' => 'en', 'default' => true]);
    Language::factory()->create(['code' => 'de', 'default' => false]);

    $this->get(route('panel.settings.languages.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'delete'])
            ->where('languages.data.0._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
            ->where('languages.data.1._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
        );
});

test('a language can be created', function () {
    $this->post(route('panel.settings.languages.store'), [
        'code' => 'fr',
        'name' => 'French',
    ])->assertRedirect(route('panel.settings.languages.index'))
        ->assertSessionHas('success');

    $language = Language::where('code', 'fr')->first();

    expect($language)->not->toBeNull();
    expect($language->name)->toBe('French');
    expect($language->default)->toBeFalse();
});

test('creating a second language as default un-defaults the first', function () {
    $first = Language::factory()->create(['code' => 'en', 'default' => true]);

    $this->post(route('panel.settings.languages.store'), [
        'code' => 'fr',
        'name' => 'French',
        'default' => true,
    ])->assertRedirect(route('panel.settings.languages.index'));

    expect($first->fresh()->default)->toBeFalse();
    expect(Language::where('code', 'fr')->first()->default)->toBeTrue();
    expect(Language::where('default', true)->count())->toBe(1);
});

test('code must be unique', function () {
    Language::factory()->create(['code' => 'en']);

    $this->post(route('panel.settings.languages.store'), [
        'code' => 'en',
        'name' => 'English again',
    ])->assertSessionHasErrors('code');
});

test('the language edit screen renders with the language data', function () {
    $language = Language::factory()->create(['code' => 'de', 'name' => 'German', 'default' => false]);

    $this->get(route('panel.settings.languages.edit', $language))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/languages/Edit')
            ->where('language.id', $language->id)
            ->where('language.code', 'de')
            ->where('language.name', 'German')
            ->where('language.default', false)
            ->where('hasUrls', false)
            ->has('urls.update')
            ->has('urls.destroy')
            ->has('urls.index')
        );
});

test('a language can be updated', function () {
    $language = Language::factory()->create(['code' => 'de', 'name' => 'German', 'default' => false]);

    $this->put(route('panel.settings.languages.update', $language), [
        'code' => 'de',
        'name' => 'Deutsch',
    ])->assertRedirect(route('panel.settings.languages.index'))
        ->assertSessionHas('success');

    expect($language->fresh()->name)->toBe('Deutsch');
});

test('updating a language to default un-defaults whichever language was default', function () {
    $default = Language::factory()->create(['code' => 'en', 'default' => true]);
    $language = Language::factory()->create(['code' => 'de', 'default' => false]);

    $this->put(route('panel.settings.languages.update', $language), [
        'code' => 'de',
        'name' => 'German',
        'default' => true,
    ])->assertRedirect(route('panel.settings.languages.index'));

    expect($default->fresh()->default)->toBeFalse();
    expect($language->fresh()->default)->toBeTrue();
    expect(Language::where('default', true)->count())->toBe(1);
});

test('unsetting default on the default language is rejected with a flash error', function () {
    $language = Language::factory()->create(['code' => 'en', 'default' => true]);

    $this->from(route('panel.settings.languages.edit', $language))
        ->put(route('panel.settings.languages.update', $language), [
            'code' => 'en',
            'name' => 'English',
            'default' => false,
        ])->assertRedirect(route('panel.settings.languages.edit', $language))
        ->assertSessionHas('error', __('panel::languages.default_unset_blocked'));

    expect($language->fresh()->default)->toBeTrue();
});

test('the default language cannot be deleted and shows a flash error', function () {
    $language = Language::factory()->create(['default' => true]);

    $this->from(route('panel.settings.languages.edit', $language))
        ->delete(route('panel.settings.languages.destroy', $language))
        ->assertRedirect(route('panel.settings.languages.edit', $language))
        ->assertSessionHas('error', __('panel::languages.delete_blocked_default'));

    expect(Language::find($language->id))->not->toBeNull();
});

test('a language with no URLs can be deleted', function () {
    $language = Language::factory()->create(['default' => false]);

    $this->delete(route('panel.settings.languages.destroy', $language))
        ->assertRedirect(route('panel.settings.languages.index'))
        ->assertSessionHas('success');

    expect(Language::find($language->id))->toBeNull();
});

test('a language with URLs cannot be deleted and shows a flash error', function () {
    Language::factory()->create(['code' => 'en', 'default' => true]);
    $language = Language::factory()->create(['code' => 'de', 'default' => false]);

    $brand = Brand::factory()->create();

    Url::factory()->create([
        'language_id' => $language->id,
        'element_type' => $brand->getMorphClass(),
        'element_id' => $brand->id,
    ]);

    $this->from(route('panel.settings.languages.edit', $language))
        ->delete(route('panel.settings.languages.destroy', $language))
        ->assertRedirect(route('panel.settings.languages.edit', $language))
        ->assertSessionHas('error', __('panel::languages.delete_blocked'));

    expect(Language::find($language->id))->not->toBeNull();
});
