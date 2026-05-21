<?php

use Livewire\Livewire;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('support.relationManagers');

it('can render relationship manager', function () {
    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    Language::factory()->create([
        'default' => true,
    ]);

    $product = Product::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(ChannelRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'customerGroupRelationManager',
    ])->assertSuccessful();
});
