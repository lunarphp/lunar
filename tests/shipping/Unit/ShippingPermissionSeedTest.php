<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Support\Facades\LunarAccessControl;
use Lunar\Tests\Shipping\TestCase;
use Spatie\Permission\Models\Permission;

uses(TestCase::class, RefreshDatabase::class)->group('shipping');

test('the shipping:manage permission is seeded against the core staff guard without the Filament admin installed', function () {
    expect(app()->bound('lunar-panel'))->toBeFalse();

    expect(
        Permission::query()
            ->where('name', 'shipping:manage')
            ->where('guard_name', LunarAccessControl::getAuthGuard())
            ->exists()
    )->toBeTrue();
});
