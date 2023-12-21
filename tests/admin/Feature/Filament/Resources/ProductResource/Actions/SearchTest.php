<?php
use Illuminate\Support\Facades\DB;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product.search');

it('can search product by name on brand list', function () {

    $this->asStaff(admin: true);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $products = \Lunar\Models\Product::factory()->count(10)->create();

    $products->each(function ($product) {
        \Lunar\Models\ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);
    });

    $name = $products->first()->translateAttribute('name');
    // dump($products->first());
    // dump($products->first()->attribute_data['name']);
    //dd(DB::raw('attribute_data'));
    // dd(\Lunar\Models\Product::where('attribute_data', 'like', '%porro%')->get() );
    //dd(\Lunar\Models\Product::whereJsonContains('attribute_data', ['porro'])->get() );
    // dd($name);
    dump('Search :' . $name);
  //  dump($products->where('attribute_data->name->value', $name));

  $resultSearch = [];
    $products->map(function ($product) use ($resultSearch, $name) {
         if ($name == $product->translateAttribute('name')) array_push($resultSearch, $product);
    });
    //dd($products);
    
    //dd( $products->where('attribute_data', "LIKE", "%".($name)."%") );
    \Livewire\Livewire::test(Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts::class)
        ->searchTableColumns($name)
        ->assertCanSeeTableRecords($resultSearch);
});