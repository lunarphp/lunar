<?php

namespace Lunar\Hub\Tests\Unit\Http\Livewire\Components\Settings\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Lunar\Hub\Http\Livewire\Components\Settings\Product\Options\OptionsIndex;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Lunar\Models\Language;
use Lunar\Models\ProductOption;

class ProductOptionCreateTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    Language::factory()->create([
      'default' => true,
      'code' => 'en',
    ]);
  }

  /**
   * @test
   * */
  public function can_populate_product_option_data()
  {
    $staff = Staff::factory()->create([
      'admin' => true,
    ]);

    LiveWire::actingAs($staff, 'staff')
      ->test(OptionsIndex::class)
      ->set('newProductOption.name.' . Language::getDefault()->code, 'Size')
      ->call('createOption');

    $this->assertDatabaseHas((new ProductOption())->getTable(), [
      'name' => json_encode([Language::getDefault()->code => 'Size']),
    ]);
  }
}
