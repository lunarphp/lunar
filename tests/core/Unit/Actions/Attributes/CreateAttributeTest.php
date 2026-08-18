<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\CreateAttribute;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates an attribute with the given attributes', function () {
    $attribute = app(CreateAttribute::class)->execute([
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'text',
    ]);

    expect($attribute)->toBeInstanceOf(Attribute::class);

    $this->assertDatabaseHas('lunar_attributes', [
        'id' => $attribute->id,
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'text',
        'system' => false,
    ]);
});

test('attaches the given model types', function () {
    $attribute = app(CreateAttribute::class)->execute([
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'text',
        'model_types' => ['product', 'brand', 'product'],
    ]);

    expect($attribute->models()->pluck('model_type')->sort()->values()->all())->toBe(['brand', 'product']);
});

test('appends to the end of the position order by default', function () {
    Attribute::factory()->create(['position' => 7]);

    $attribute = app(CreateAttribute::class)->execute([
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'text',
    ]);

    expect($attribute->position)->toBe(8);
});
