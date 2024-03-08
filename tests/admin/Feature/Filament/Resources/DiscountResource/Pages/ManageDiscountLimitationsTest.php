<?php

use function Pest\Laravel\{get};

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can render discount limitations page', function () {
    $record = \Lunar\Models\Discount::factory()->create();

    \Lunar\Models\Channel::factory()->create(['default' => true]);

    get(\Lunar\Admin\Filament\Resources\DiscountResource::getUrl('limitations', [
        'record' => $record,
    ]))->assertSuccessful();
});
