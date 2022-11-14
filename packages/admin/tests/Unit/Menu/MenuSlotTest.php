<?php

namespace Lunar\Hub\Tests\Unit\Menu;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Hub\Auth\Manifest;
use Lunar\Hub\Menu\MenuSlot;
use Lunar\Hub\Models\Staff;
use Lunar\Hub\Tests\TestCase;

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

        $staff->permissions()->create([
            'handle' => 'item-one-gate',
        ]);

        $manifest = $this->app->make(Manifest::class);
        $manifest->addPermission(function ($perm) {
            $perm->handle('item-one-gate');
        });

        $this->assertCount(2, $slot->getItems());
    }

    /** @test */
    public function can_remove_a_item_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $item = $slot->getItems()->first();

        $this->assertCount(1, $slot->getItems());

        $slot->removeItem($item->handle);

        $this->assertCount(0, $slot->getItems());
    }

    /** @test */
    public function can_remove_a_section_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getSections());

        $section = $slot
            ->section('section-one')
            ->name('Section One');

        $this->assertCount(1, $slot->getSections());

        $slot->removeSection($section->getHandle());

        $this->assertCount(0, $slot->getSections());
    }

    /** @test */
    public function can_remove_a_item_from_a_section_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getSections());

        $section = $slot
            ->section('section-one')
            ->name('Section One');

        $section->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $item = $section->getItems()->first();

        $this->assertCount(1, $section->getItems());

        $slot->removeSectionItem($section->getHandle(), $item->handle);

        $this->assertCount(0, $section->getItems());
    }

    /** @test */
    public function can_update_a_item_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $item = $slot->getItems()->first();

        $this->assertCount(1, $slot->getItems());

        $this->assertEquals('Item One', $item->name);

        $slot->updateItem($item->handle, ['name' => 'Item A']);

        $this->assertEquals('Item A', $item->name);
    }

    /** @test */
    public function can_update_a_section_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getSections());

        $section = $slot
            ->section('section-one')
            ->name('Section One');

        $this->assertCount(1, $slot->getSections());

        $this->assertEquals('Section One', $section->name);

        $slot->updateSection($section->getHandle(), ['name' => 'Section A']);

        $this->assertEquals('Section A', $section->name);
    }

    /** @test */
    public function can_update_a_item_from_a_section_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getSections());

        $section = $slot
            ->section('section-one')
            ->name('Section One');

        $section->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $section->addItem(function ($item) {
            $item
                ->handle('item-two')
                ->name('Item Two');
        });

        $itemA = $section->getItems()->first();
        $itemB = $section->getItems()->last();

        $this->assertCount(2, $section->getItems());

        $slot->updateSectionItem($section->getHandle(), $itemA->handle, ['name' => 'Item A']);
        $slot->updateSectionItem($section->getHandle(), $itemB->handle, ['name' => 'Item B']);

        $this->assertEquals('Item A', $itemA->name);
        $this->assertEquals('Item B', $itemB->name);
    }

    /** @test */
    public function can_order_items_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getItems());

        $slot->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $slot->addItem(function ($item) {
            $item
                ->handle('item-two')
                ->name('Item Two');
        });

        $itemA = $slot->getItems()->first();
        $itemB = $slot->getItems()->last();

        $this->assertCount(2, $slot->getItems());

        $slot->updateItem($itemA->handle, ['position' => 'last']);
        $slot->updateItem($itemB->handle, ['position' => 1]);

        $this->assertEquals('last', $itemA->position);
        $this->assertEquals(1, $itemB->position);
    }

    /** @test */
    public function can_order_sections_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $this->assertCount(0, $slot->getItems());

        $sectionA = $slot
            ->section('section-one')
            ->name('Section One');

        $sectionB = $slot
            ->section('section-two')
            ->name('Section Two');

        $this->assertCount(2, $slot->getSections());

        $slot->updateSection($sectionA->getHandle(), ['position' => 'last']);
        $slot->updateSection($sectionB->getHandle(), ['position' => 1]);

        $this->assertEquals('last', $sectionA->position);
        $this->assertEquals(1, $sectionB->position);
    }

    /** @test */
    public function can_order_items_from_a_section_from_the_slot()
    {
        $slot = new MenuSlot('foo-bar');

        $section = $slot
        ->section('section-one')
        ->name('Section One');

        $section->addItem(function ($item) {
            $item
                ->handle('item-one')
                ->name('Item One');
        });

        $section->addItem(function ($item) {
            $item
                ->handle('item-two')
                ->name('Item Two');
        });

        $this->assertCount(2, $section->getItems());

        $itemA = $section->getItems()->first();
        $itemB = $section->getItems()->last();

        $slot->updateSectionItem($section->getHandle(), $itemA->handle, ['position' => 'last']);
        $slot->updateSectionItem($section->getHandle(), $itemB->handle, ['position' => 1]);

        $this->assertEquals('last', $itemA->position);
        $this->assertEquals(1, $itemB->position);
    }
}
