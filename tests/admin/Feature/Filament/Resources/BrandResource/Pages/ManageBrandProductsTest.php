<?php

use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.collection');

it('can render the brand products page', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Brand::factory()->create();

    $this->asStaff(admin: true)
        ->get(BrandResource::getUrl('products', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});
