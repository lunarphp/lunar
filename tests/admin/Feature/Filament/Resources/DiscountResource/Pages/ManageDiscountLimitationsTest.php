<?php

use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

use function Pest\Laravel\get;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can render discount limitations page', function () {
    $record = Discount::factory()->create();

    Channel::factory()->create(['default' => true]);

    get(DiscountResource::getUrl('limitations', [
        'record' => $record,
    ]))->assertSuccessful();
});
