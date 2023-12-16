<?php

namespace Lunar\Tests\Unit\Search;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Customer;
use Lunar\Models\Language;
use Lunar\Search\CustomerIndexer;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;

/**
 * @group lunar.search
 * @group lunar.search.customer
 */
class CustomerIndexerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_return_correct_searchable_data()
    {
        Language::factory()->create([
            'code' => 'en',
            'default' => true,
        ]);

        Language::factory()->create([
            'code' => 'dk',
            'default' => false,
        ]);

        $attributeA = Attribute::factory()->create([
            'attribute_type' => Customer::class,
            'searchable' => true,
        ]);
        $attributeB = Attribute::factory()->create([
            'attribute_type' => Customer::class,
            'searchable' => true,
        ]);
        $attributeC = Attribute::factory()->create([
            'attribute_type' => Customer::class,
            'searchable' => false,
        ]);
        $attributeD = Attribute::factory()->create([
            'attribute_type' => Customer::class,
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

        $this->assertEquals($customer->fullName, $data['name']);
        $this->assertEquals($customer->company_name, $data['company_name']);
        $this->assertEquals($customer->vat_no, $data['vat_no']);
        $this->assertArrayHasKey('meta_field', $data);
        $this->assertEquals('meta_value', $data['meta_field']);
        $this->assertEquals($customer->account_ref, $data['account_ref']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey($attributeA->handle, $data);
        $this->assertArrayHasKey($attributeB->handle, $data);
        $this->assertArrayNotHasKey($attributeC->handle, $data);
        $this->assertArrayHasKey($attributeD->handle.'_en', $data);
        $this->assertArrayHasKey($attributeD->handle.'_dk', $data);
        $this->assertSame([$user->email], $data['user_emails']);
    }
}
