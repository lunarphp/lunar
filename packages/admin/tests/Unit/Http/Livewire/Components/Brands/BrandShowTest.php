<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Brands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\FieldTypes\Text;
use Lunar\Hub\Http\Livewire\Components\Brands\BrandShow;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Currency;
use Lunar\Models\Language;

/**
 * @group hub.brands
 */
class BrandShowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create([
            'default' => true,
        ]);

        Language::factory()->create([
            'default' => true,
            'code' => 'en',
        ]);
    }

    /** @test  */
    public function component_mounts_correctly()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $brand = Brand::factory()->create();

        LiveWire::actingAs($staff, 'staff')
            ->test(BrandShow::class, [
                'brand' => $brand,
            ])->assertSet('brand', $brand);
    }

    /** @test  */
    public function correct_brand_is_loaded()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        $brand = Brand::factory()->create();

        LiveWire::actingAs($staff, 'staff')
            ->test(BrandShow::class, [
                'brand' => $brand,
            ])->assertSet('brand.id', $brand->id);
    }

    /** @test */
    public function can_set_brand_attribute_data()
    {
        $staff = Staff::factory()->create([
            'admin' => true,
        ]);

        // Need some attributes...
        $description = Attribute::factory()->create([
            'handle' => 'description',
            'attribute_type' => 'Lunar\Models\Brand',
        ]);

        $brand = Brand::factory()->create();

        $brand->mappedAttributes()->saveMany(Attribute::get());

        $component = LiveWire::actingAs($staff, 'staff')
            ->test(BrandShow::class, [
                'brand' => $brand,
            ])
            ->set('attributeMapping.'.'a_'.$description->id.'.data', 'nouseforadescription')
            ->call('addUrl')
            ->set('urls.0.slug', 'foo-bar')
            ->call('update')
            ->assertHasNoErrors();

        $newData = $brand->refresh()->attribute_data;

        $description = $newData['description'];

        $this->assertInstanceOf(Text::class, $description);

        $this->assertEquals('nouseforadescription', $description->getValue());
    }
}
