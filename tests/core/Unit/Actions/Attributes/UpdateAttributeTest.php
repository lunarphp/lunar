<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\UpdateAttribute;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the attribute attributes', function () {
    $attribute = Attribute::factory()->create(['name' => 'Old Name']);

    app(UpdateAttribute::class)->execute($attribute, ['name' => 'New Name', 'required' => true]);

    $this->assertDatabaseHas('lunar_attributes', [
        'id' => $attribute->id,
        'name' => 'New Name',
        'required' => true,
    ]);
});

test('replaces the model types when supplied', function () {
    $attribute = Attribute::factory()->create();
    $attribute->models()->create(['model_type' => 'product']);

    app(UpdateAttribute::class)->execute($attribute, ['model_types' => ['brand']]);

    expect($attribute->models()->pluck('model_type')->all())->toBe(['brand']);
});

test('keeps the model types when none are supplied', function () {
    $attribute = Attribute::factory()->create();
    $attribute->models()->create(['model_type' => 'product']);

    app(UpdateAttribute::class)->execute($attribute, ['name' => 'Renamed']);

    expect($attribute->models()->pluck('model_type')->all())->toBe(['product']);
});
