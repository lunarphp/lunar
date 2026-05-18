<?php

use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Models\Channel;
use Lunar\Models\Discount;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

use function Pest\Laravel\get;

uses(TestCase::class)
    ->group('resource.discount');

beforeEach(function () {
    $this->asStaff();
});

it('can render discount availability page', function () {
    $record = Discount::factory()->create();

    Channel::factory()->create(['default' => true]);

    get(DiscountResource::getUrl('availability', [
        'record' => $record,
    ]))->assertSuccessful();
});
