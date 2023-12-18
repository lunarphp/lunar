<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
            'handle' => 'meta_description',
            'section' => 'product_variant',
            'type' => \Lunar\FieldTypes\Text::class,
            'required' => false,
            'default_value' => '',
            'configuration' => [
                'options' => $options,
            ],
            'system' => true,
        ]);

    expect($attribute->name->get('en'))->toEqual('Meta Description');
    expect($attribute->handle)->toEqual('meta_description');
    expect($attribute->type)->toEqual(\Lunar\FieldTypes\Text::class);
    expect($attribute->system)->toBeTrue();
    expect($attribute->position)->toEqual(4);
    expect($attribute->configuration->get('options'))->toEqual($options);
});
