<?php

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.collection');

it('can render the brand products page', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Brand::factory()->create();

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\BrandResource::getUrl('products', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});
