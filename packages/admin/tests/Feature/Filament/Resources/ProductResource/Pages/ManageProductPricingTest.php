<?php

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('resource.product');

it('can render product prices create page', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Product::factory()->create();

    $this->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\ProductResource::getUrl('pricing', [
            'record' => $record,
        ]))
        ->assertSuccessful();
});
