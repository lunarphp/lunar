<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\FieldTypes\AbstractFieldType;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Jobs\Attributes\PurgeAttributeData;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('attribute data hydrates to a handle-keyed collection of field types', function () {
    Attribute::factory()->create(['handle' => 'meta_title', 'type' => FieldTypeEnum::Text->value]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'meta_title' => new Text('Widget'),
        ],
    ]);

    $hydrated = $product->refresh()->attribute_data;

    expect($hydrated->keys()->toArray())->toBe(['meta_title']);
    expect($hydrated->get('meta_title'))->toBeInstanceOf(Text::class);
    expect($product->attr('meta_title'))->toBe('Widget');
});

test('attribute data persists keyed by attribute id', function () {
    $attribute = Attribute::factory()->create(['handle' => 'meta_title', 'type' => FieldTypeEnum::Text->value]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'meta_title' => new Text('Widget'),
        ],
    ]);

    $raw = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    expect(json_decode($raw, true))->toBe([
        (string) $attribute->id => 'Widget',
    ]);
});

test('renaming an attribute handle does not change stored data', function () {
    $attribute = Attribute::factory()->create(['handle' => 'meta_title', 'type' => FieldTypeEnum::Text->value]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'meta_title' => new Text('Widget'),
        ],
    ]);

    $rawBefore = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    $attribute->update(['handle' => 'seo_title']);

    $rawAfter = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    expect($rawAfter)->toBe($rawBefore);
    expect($product->fresh()->attr('seo_title'))->toBe('Widget');
});

test('deleting an attribute purges its id from attribute data', function () {
    $attribute = Attribute::factory()->create(['handle' => 'meta_title', 'type' => FieldTypeEnum::Text->value]);

    $product = Product::factory()->create([
        'attribute_data' => [
            'meta_title' => new Text('Widget'),
        ],
    ]);

    $attribute->delete();

    (new PurgeAttributeData($attribute->id))->handle();

    $raw = DB::table('lunar_products')->where('id', $product->id)->value('attribute_data');

    expect(json_decode($raw, true))->toBe([]);
});

test('a custom field type round-trips through the cast', function () {
    FieldTypeManifest::add('uppercase', UppercaseFieldType::class);

    Attribute::factory()->create(['handle' => 'shout', 'type' => 'uppercase']);

    $product = Product::factory()->create([
        'attribute_data' => [
            'shout' => new UppercaseFieldType('hello'),
        ],
    ]);

    $hydrated = $product->refresh()->attribute_data->get('shout');

    expect($hydrated)->toBeInstanceOf(UppercaseFieldType::class);
    expect($hydrated->getValue())->toBe('HELLO');
});

class UppercaseFieldType extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        $this->value = $value === null ? null : strtoupper((string) $value);
    }
}
