<?php

use Livewire\Livewire;
use Lunar\Admin\Support\RelationManagers\ChannelRelationManager;

uses(\Lunar\Tests\Admin\Unit\Filament\TestCase::class)
    ->group('support.relationManagers');

it('can render relationship manager', function () {
    \Lunar\Models\CustomerGroup::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $product = \Lunar\Models\Product::factory()->create();

    $this->asStaff(admin: true);

    Livewire::test(ChannelRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass' => 'customerGroupRelationManager',
    ])->assertSuccessful();
});
