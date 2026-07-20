<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function brandAttribute(array $state = []): Attribute
{
    return Attribute::factory()
        ->has(AttributeModel::factory()->state(['model_type' => 'brand']), 'models')
        ->create($state);
}

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true]);

    $this->brand = Brand::factory()->create();
});

it('serves the attribute schema and values on the edit page', function () {
    $group = AttributeGroup::factory()->create(['name' => 'Storefront']);

    brandAttribute([
        'handle' => 'hero_cta',
        'name' => 'Hero CTA',
        'type' => 'text',
        'attribute_group_id' => $group->id,
        'required' => false,
    ]);

    $this->brand->update(['attribute_data' => ['hero_cta' => 'Shop the range']]);

    $this->get(route('panel.brands.edit', $this->brand))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('attributeGroups', 1)
            ->where('attributeGroups.0.name', 'Storefront')
            ->where('attributeGroups.0.fields.0.handle', 'hero_cta')
            ->where('attributeGroups.0.fields.0.type', 'text')
            ->where('attributeValues.attribute:hero_cta', 'Shop the range')
        );
});

it('serves dropdown options for row and legacy map lookup shapes', function () {
    brandAttribute([
        'handle' => 'finish',
        'type' => 'dropdown',
        'required' => false,
        'attribute_group_id' => null,
        'position' => 1,
        'configuration' => ['lookups' => [['label' => 'Matte', 'value' => 'matte']]],
    ]);

    // Earlier panel builds stored lookups as a label => value map.
    brandAttribute([
        'handle' => 'material',
        'type' => 'dropdown',
        'required' => false,
        'attribute_group_id' => null,
        'position' => 2,
        'configuration' => ['lookups' => ['Glass' => 'glass']],
    ]);

    $this->get(route('panel.brands.edit', $this->brand))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('attributeGroups.0.fields.0.config.options.0', ['label' => 'Matte', 'value' => 'matte'])
            ->where('attributeGroups.0.fields.1.config.options.0', ['label' => 'Glass', 'value' => 'glass'])
        );
});

it('drafts and commits an attribute value alongside scalar fields', function () {
    brandAttribute(['handle' => 'hero_cta', 'type' => 'text', 'required' => false]);
    brandAttribute(['handle' => 'strapline', 'type' => 'text', 'required' => false]);

    $this->brand->update(['attribute_data' => ['strapline' => 'Untouched value']]);

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['attribute:hero_cta' => 'Shop the range', 'name' => 'Renamed'],
    ])->assertOk();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    $this->brand->refresh();

    expect($this->brand->name)->toBe('Renamed')
        ->and($this->brand->attr('hero_cta'))->toBe('Shop the range')
        // Attributes the draft never touched survive the whole-column write.
        ->and($this->brand->attr('strapline'))->toBe('Untouched value');
});

it('validates attribute values on commit', function () {
    brandAttribute([
        'handle' => 'finish',
        'type' => 'dropdown',
        'required' => false,
        'configuration' => collect(['lookups' => [['label' => 'Matte', 'value' => 'matte']]]),
    ]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['attribute:finish' => 'glossy'],
        'rebase' => [],
    ])->assertUnprocessable();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['attribute:finish' => 'matte'],
        'rebase' => [],
    ])->assertOk();

    expect($this->brand->refresh()->attr('finish'))->toBe('matte');
});

it('round-trips keyed list values from the Filament admin without reordering', function () {
    brandAttribute(['handle' => 'specs', 'type' => 'list', 'required' => false]);

    // The Filament admin's KeyValue editor stores list values as ordered key => value maps.
    $this->brand->update(['attribute_data' => ['specs' => ['width' => '10cm', 'depth' => '5cm', 'colour' => 'red']]]);

    $this->get(route('panel.brands.edit', $this->brand))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('attributeValues.attribute:specs', ['width' => '10cm', 'depth' => '5cm', 'colour' => 'red'])
        );

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['attribute:specs' => ['width' => '12cm', 'depth' => '5cm', 'colour' => 'red']],
    ])->assertOk();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($this->brand->refresh()->attr('specs'))->toBe(['width' => '12cm', 'depth' => '5cm', 'colour' => 'red']);
});

it('enforces max_items on list attribute values at commit', function () {
    brandAttribute([
        'handle' => 'tags',
        'type' => 'list',
        'required' => false,
        'configuration' => ['max_items' => 2],
    ]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['attribute:tags' => ['a', 'b', 'c']],
        'rebase' => [],
    ])->assertUnprocessable();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['attribute:tags' => ['a', 'b']],
        'rebase' => [],
    ])->assertOk();

    expect($this->brand->refresh()->attr('tags'))->toBe(['a', 'b']);
});

it('commits while a stored translated attribute value exists untouched', function () {
    brandAttribute(['handle' => 'story', 'type' => 'translated_text', 'required' => false]);
    brandAttribute(['handle' => 'hero_cta', 'type' => 'text', 'required' => false]);

    $this->brand->update(['attribute_data' => ['story' => ['en' => 'Our story']]]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['attribute:hero_cta' => 'Shop'],
        'rebase' => [],
    ])->assertOk();

    $this->brand->refresh();

    expect($this->brand->attr('hero_cta'))->toBe('Shop')
        ->and($this->brand->attr('story'))->toBe('Our story');
});

it('detects conflicts per attribute field', function () {
    brandAttribute(['handle' => 'hero_cta', 'type' => 'text', 'required' => false]);

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['attribute:hero_cta' => 'Mine'],
    ])->assertOk();

    $this->brand->update(['attribute_data' => ['hero_cta' => 'Theirs']]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'attribute:hero_cta');
});

it('normalises translated attribute maps when drafting', function () {
    brandAttribute(['handle' => 'story', 'type' => 'translated_text', 'required' => false]);

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['attribute:story' => ['fr' => 'Histoire', 'en' => 'Story', 'de' => '']],
    ])->assertOk();

    expect(EditDraft::sole()->data['attribute:story'])->toBe([
        'en' => 'Story',
        'fr' => 'Histoire',
    ]);
});
