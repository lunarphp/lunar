<?php

use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.product');

it('can render product urls create page', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Product::factory()->create();

    $this->asStaff(admin: true)
        ->get(ProductResource::getUrl('urls', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});
