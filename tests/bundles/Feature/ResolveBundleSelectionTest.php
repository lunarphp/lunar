<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Bundles\Exceptions\InvalidBundleSelection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Bundles\ValueObjects\BundleSelection;
use Lunar\Bundles\ValueObjects\SelectedComponent;
use Lunar\Tests\Bundles\Support\Catalogue;
use Lunar\Tests\Bundles\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Catalogue::store();

    $this->bundle = Bundle::factory()->create();
    $this->fixed = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'quantity' => 2]);
    $this->lens = BundleGroup::factory()->create(['bundle_id' => $this->bundle->id, 'name' => ['en' => 'Lens'], 'min_selections' => 1, 'max_selections' => 2]);
    $this->lensA = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'bundle_group_id' => $this->lens->id, 'default' => true]);
    $this->lensB = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'bundle_group_id' => $this->lens->id]);
    $this->bag = BundleGroup::factory()->create(['bundle_id' => $this->bundle->id, 'name' => ['en' => 'Bag'], 'min_selections' => 0, 'max_selections' => 1]);
    $this->bagA = BundleComponent::factory()->create(['bundle_id' => $this->bundle->id, 'bundle_group_id' => $this->bag->id]);
});

test('empty meta resolves to the default selection', function () {
    $selection = $this->bundle->resolveSelection();

    expect($selection)->toBeInstanceOf(BundleSelection::class)
        ->and($selection->isDefault())->toBeTrue()
        ->and($selection->map(fn (SelectedComponent $component) => $component->component->id)->all())
        ->toBe([$this->fixed->id, $this->lensA->id])
        ->and($selection->components->first()->quantity)->toBe(2)
        ->and($selection->components->first()->group)->toBeNull()
        ->and($selection->components->last()->group->is($this->lens))->toBeTrue()
        ->and($selection->variants()->pluck('id')->all())->toBe([$this->fixed->product_variant_id, $this->lensA->product_variant_id])
        ->and(count($selection))->toBe(2);
});

test('an explicit selection is honoured and is not the default', function () {
    $selection = $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [$this->lensB->public_id, $this->lensA->public_id],
        $this->bag->public_id => [$this->bagA->public_id],
    ]]]);

    expect($selection->isDefault())->toBeFalse()
        ->and($selection->map(fn (SelectedComponent $component) => $component->component->id)->all())
        ->toBe([$this->fixed->id, $this->lensB->id, $this->lensA->id, $this->bagA->id]);
});

test('choosing exactly the defaults is still the default selection', function () {
    $selection = $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [$this->lensA->public_id],
    ]]]);

    expect($selection->isDefault())->toBeTrue();
});

test('an unknown group is invalid', function () {
    $this->bundle->resolveSelection(['bundle' => ['selections' => ['nope' => [$this->lensA->public_id]]]]);
})->throws(InvalidBundleSelection::class);

test('a component from another group or bundle is invalid', function () {
    expect(fn () => $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [$this->bagA->public_id],
    ]]]))->toThrow(InvalidBundleSelection::class);

    $foreign = BundleComponent::factory()->create();

    expect(fn () => $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [$foreign->public_id],
    ]]]))->toThrow(InvalidBundleSelection::class);
});

test('duplicates and counts outside min and max are invalid', function () {
    expect(fn () => $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [$this->lensA->public_id, $this->lensA->public_id],
    ]]]))->toThrow(InvalidBundleSelection::class);

    expect(fn () => $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->lens->public_id => [],
    ]]]))->toThrow(InvalidBundleSelection::class);

    expect(fn () => $this->bundle->resolveSelection(['bundle' => ['selections' => [
        $this->bag->public_id => [$this->bagA->public_id, $this->bagA->public_id],
    ]]]))->toThrow(InvalidBundleSelection::class);
});

test('a required group without defaults cannot be omitted', function () {
    $this->lensA->update(['default' => false]);

    $this->bundle->fresh()->resolveSelection();
})->throws(InvalidBundleSelection::class);

test('malformed meta is invalid', function () {
    $this->bundle->resolveSelection(['bundle' => ['selections' => 'lens']]);
})->throws(InvalidBundleSelection::class);
