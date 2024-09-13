<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Search\CustomerIndexer;
use Lunar\Tests\Core\Stubs\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return correct searchable data', function () {
    Language::factory()->create([
        'code' => 'en',
        'default' => true,
    ]);

    Language::factory()->create([
        'code' => 'dk',
        'default' => false,
    ]);

    $attributeA = Attribute::factory()->create([
        'attribute_type' => 'customer',
        'searchable' => true,
    ]);
    $attributeB = Attribute::factory()->create([
        'attribute_type' => 'customer',
        'searchable' => true,
    ]);
    $attributeC = Attribute::factory()->create([
        'attribute_type' => 'customer',
        'searchable' => false,
    ]);
    $attributeD = Attribute::factory()->create([
        'attribute_type' => 'customer',
        'type' => TranslatedText::class,
        'searchable' => true,
    ]);

    $customer = Customer::factory()->create([
        'attribute_data' => collect([
            $attributeA->handle => new Text('Attribute A'),
            $attributeB->handle => new Text('Attribute B'),
            $attributeC->handle => new Text('Attribute C'),
            $attributeD->handle => new TranslatedText([
                'en' => 'Attribute D EN',
                'dk' => 'Attribute D DK',
            ]),
        ]),
        'meta' => [
            'meta_field' => 'meta_value',
        ],
    ]);

    $user = User::factory()->create();

    $customer->users()->attach($user);

    $data = app(CustomerIndexer::class)->toSearchableArray($customer);

    expect($data['name'])->toEqual($customer->fullName);
    expect($data['company_name'])->toEqual($customer->company_name);
    expect($data['vat_no'])->toEqual($customer->vat_no);
    expect($data)->toHaveKey('meta_field');
    expect($data['meta_field'])->toEqual('meta_value');
    expect($data['account_ref'])->toEqual($customer->account_ref);
    expect($data)->toHaveKey('id');
    expect($data)->toHaveKey($attributeA->handle);
    expect($data)->toHaveKey($attributeB->handle);
    $this->assertArrayNotHasKey($attributeC->handle, $data);
    expect($data)->toHaveKey($attributeD->handle.'_en');
    expect($data)->toHaveKey($attributeD->handle.'_dk');
    expect($data['user_emails'])->toBe([$user->email]);
});
