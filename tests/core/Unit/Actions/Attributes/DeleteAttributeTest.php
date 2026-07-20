<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\DeleteAttribute;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a non-system attribute along with its model links', function () {
    $attribute = Attribute::factory()->create(['system' => false]);
    $attribute->models()->create(['model_type' => 'product']);

    app(DeleteAttribute::class)->execute($attribute);

    $this->assertDatabaseMissing('lunar_attributes', ['id' => $attribute->id]);
    $this->assertDatabaseMissing('lunar_attribute_models', ['attribute_id' => $attribute->id]);
});

test('refuses to delete a system attribute', function () {
    $attribute = Attribute::factory()->create(['system' => true]);

    expect(fn () => app(DeleteAttribute::class)->execute($attribute))
        ->toThrow(AttributeActionException::class);

    $this->assertDatabaseHas('lunar_attributes', ['id' => $attribute->id]);
});
