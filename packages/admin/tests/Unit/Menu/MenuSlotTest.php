<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Auth\Manifest;
use Lunar\Hub\Menu\MenuSlot;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;
use Spatie\Permission\Models\Permission;

/**
 * @group hub.menu
 */
class MenuSlotTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_initialise_the_class()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertEquals('foo-bar', $slot->getHandle());
    }

    /** @test */
    public function can_add_a_new_item_to_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item->name('Item One');
        });

        $this->assertCount(1, $slot->getItems());
    }

    /** @test */
    public function can_get_slot_has_links()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertFalse($slot->hasLinks());

        $slot->addItem(function ($item) {
            $item->name('Item One');
        });

        $this->assertTrue($slot->hasLinks());

        $slot = new MenuSlot('bar-foo');

        $this->assertCount(0, $slot->getItems());
        $this->assertFalse($slot->hasLinks());

        $slot->section('bar-baz')
            ->addItem(function ($item) {
                $item->name('Item One');
            });

        $this->assertCount(0, $slot->getItems());
        $this->assertTrue($slot->hasLinks());
    }

    /** @test */
    public function can_get_first_item_from_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $staff = Staff::factory()->create();

        $this->actingAs($staff, 'staff');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item->name('Item One');
        });

        $slot->addItem(function ($item) {
            $item->name('Item Two');
        });

        $this->assertCount(2, $slot->getItems());

        $this->assertEquals('Item One', $slot->getFirstLink()->name);
    }

    /** @test */
    public function filters_items_based_on_user_permissions()
    {
        $slot = new MenuSlot('foo-bar');

        $staff = Staff::factory()->create();

        $this->actingAs($staff, 'staff');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item->name('Item One');
        });

        $slot->addItem(function ($item) {
            $item->name('Gated Item')->gate('item-one-gate');
        });

        $this->assertCount(1, $slot->getItems());

        $manifest = $this->app->make(Manifest::class);
        $manifest->addPermission(function ($perm) {
            $perm->handle('item-one-gate');
        });

        $perm = Permission::firstOrCreate([
            'name' => 'item-one-gate',
            'guard_name' => 'staff',
        ]);

        $staff->givePermissionTo($perm);

        $this->assertCount(2, $slot->getItems());
    }

    /** @test */
    public function can_get_first_item_based_on_user_permissions()
    {
        $slot = new MenuSlot('foo-bar');

        $staff = Staff::factory()->create();

        $this->actingAs($staff, 'staff');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item->name('Item One')->gate('item-one-gate');
        });

        $slot->addItem(function ($item) {
            $item->name('Gated Item')->gate('item-two-gate');
        });

        $this->assertCount(0, $slot->getItems());

        $manifest = $this->app->make(Manifest::class);
        $manifest->addPermission(function ($perm) {
            $perm->handle('item-two-gate');
        });

        $perm = Permission::firstOrCreate([
            'name' => 'item-two-gate',
            'guard_name' => 'staff',
        ]);

        $staff->givePermissionTo($perm);

        $this->assertCount(1, $slot->getItems());

        $this->assertEquals('Gated Item', $slot->getFirstLink()->name);
    }
}
