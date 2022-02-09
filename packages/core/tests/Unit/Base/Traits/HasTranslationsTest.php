<?php

namespace GetCandy\Tests\Unit\Console;

use GetCandy\FieldTypes\Dropdown;
use GetCandy\FieldTypes\ListField;
use GetCandy\FieldTypes\Text;
use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Product;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group traits
 */
class HasTranslationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_translate_attributes()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $this->assertEquals('English', $attributeGroup->translate('name', 'en'));
        $this->assertEquals('French', $attributeGroup->translate('name', 'fr'));

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text('English Name'),
                    'fr' => new Text('French Name'),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text('English Description'),
                    'fr' => new Text('French Description'),
                ])),
            ],
        ]);

        $this->assertEquals('English Name', $product->translateAttribute('name'));
        $this->assertEquals('French Name', $product->translateAttribute('name', 'fr'));

        $this->assertEquals('English Description', $product->translateAttribute('description'));
        $this->assertEquals('French Description', $product->translateAttribute('description', 'fr'));
    }

    /** @test */
    public function can_fallback_when_translation_not_present()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $this->assertEquals('English', $attributeGroup->translate('name', 'dk'));

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text('English Name'),
                    'fr' => new Text('French Name'),
                ])),
                'description' => new TranslatedText(collect([
                    'en' => new Text('English Description'),
                    'fr' => new Text('French Description'),
                ])),
            ],
        ]);

        $this->assertEquals('English Name', $product->translateAttribute('name', 'dk'));
    }

    /** @test */
    public function can_handle_null_values()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $this->assertEquals('English', $attributeGroup->translate('name', 'dk'));

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => null,
                ])),
                'description' => new TranslatedText(collect([
                    'en' => null,
                ])),
            ],
        ]);

        $this->assertNull($product->translateAttribute('name'));
        $this->assertNull($product->translateAttribute('description'));
    }

    /** @test */
    public function will_translate_based_on_locale_by_default()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text('English Name'),
                    'fr' => new Text('French Name'),
                ])),
            ],
        ]);

        app()->setLocale('fr');

        $this->assertEquals('French', $attributeGroup->translate('name'));
        $this->assertEquals('French Name', $product->translateAttribute('name'));

        app()->setLocale('en');

        $this->assertEquals('English', $attributeGroup->translate('name'));
        $this->assertEquals('English Name', $product->translateAttribute('name'));
    }

    /** @test */
    public function will_fallback_to_first_translation_if_nothing_exists()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text('English Name'),
                    'fr' => new Text('French Name'),
                ])),
            ],
        ]);

        app()->setLocale('dk');

        $this->assertEquals('English', $attributeGroup->translate('name'));
        $this->assertEquals('English Name', $product->translateAttribute('name'));
    }

    /** @test */
    public function will_use_fieldtype_value_if_it_doesnt_have_translations()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
            'handle' => 'some-handle',
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new Text('English Name'),
            ],
        ]);

        $this->assertEquals('some-handle', $attributeGroup->translate('handle'));
        $this->assertEquals('English Name', $product->translateAttribute('name'));
    }

    /** @test */
    public function will_return_null_if_attribute_doesnt_exist()
    {
        $attributeGroup = AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name' => new Text('English Name'),
            ],
        ]);

        $this->assertNull($attributeGroup->translate('foobar'));
        $this->assertNull($product->translateAttribute('foobar'));
    }

    /** @test */
    public function will_return_null_if_attribute_value_is_null()
    {
        AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name'        => new Text('English Name'),
                'description' => new Text(null),
            ],
        ]);

        $this->assertNull($product->translateAttribute('description'));
    }

    /**
     * @test
     * */
    public function handle_if_we_try_and_translate_a_non_translatable_attribute()
    {
        AttributeGroup::factory()->create([
            'name' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ]);

        $product = Product::factory()->create([
            'attribute_data' => [
                'name'        => new Text('Test Name'),
                'list'        => new ListField([
                    'One',
                    'Two',
                    'Three',
                ]),
                'dropdown'        => new Dropdown('Foobar')
            ],
        ]);

        $this->assertEquals('Test Name', $product->translateAttribute('name'));
        $this->assertEquals('Foobar', $product->translateAttribute('dropdown'));
        $this->assertEquals(['One', 'Two', 'Three'], $product->translateAttribute('list'));
    }
}
