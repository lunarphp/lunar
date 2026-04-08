<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('models');

use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make a attribute', function () {
    $options = [
        'Red',
        'Blue',
        'Green',
    ];

    $attribute = Attribute::factory()
        ->for(AttributeGroup::factory())
        ->create([
            'position' => 4,
            'name' => [
                'en' => 'Meta Description',
            ],
            'description' => [
                'en' => 'Meta Description',
            ],
            'handle' => 'meta_description',
            'section' => 'product_variant',
            'type' => Text::class,
            'required' => false,
            'default_value' => '',
            'configuration' => [
                'options' => $options,
            ],
            'system' => true,
        ]);

    expect($attribute->name->get('en'))->toEqual('Meta Description');
    expect($attribute->handle)->toEqual('meta_description');
    expect($attribute->type)->toEqual(Text::class);
    expect($attribute->system)->toBeTrue();
    expect($attribute->position)->toEqual(4);
    expect($attribute->configuration->get('options'))->toEqual($options);
});

test('can delete an attribute', function () {
    $options = [
        'Red',
        'Blue',
        'Green',
    ];

    $attribute = Attribute::factory()
        ->for(AttributeGroup::factory())
        ->create([
            'position' => 4,
            'name' => [
                'en' => 'Meta Description',
            ],
            'description' => [
                'en' => 'Meta Description',
            ],
            'handle' => 'meta_description',
            'section' => 'product_variant',
            'type' => Text::class,
            'required' => false,
            'default_value' => '',
            'configuration' => [
                'options' => $options,
            ],
            'system' => true,
        ]);

    DB::table('lunar_attributables')->insert([
        'attributable_type' => 'Lunar\Models\ProductType',
        'attributable_id' => 1,
        'attribute_id' => $attribute->id,
    ]);

    $attribute->delete();
    assertDatabaseMissing(Attribute::class, [
        'id' => $attribute->id,
    ]);
});
