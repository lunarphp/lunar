<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');
});

it('redirects guests to the login screen', function () {
    auth('staff')->logout();

    $this->get(route('panel.discounts.index'))->assertRedirect(route('panel.login'));
});

it('renders the discounts index with rows', function () {
    Discount::factory()->count(3)->create();

    $this->get(route('panel.discounts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('discounts/Index')
            ->has('discounts.data', 3)
            ->has('discounts.data.0', fn (Assert $row) => $row
                ->hasAll([
                    'id', 'name', 'handle', 'status', 'status_label', 'type', 'type_label',
                    'coupon', 'starts_at', 'ends_at', 'uses', 'max_uses', 'priority',
                    'edit_url', '_actions',
                ])
                ->etc()
            )
            ->has('columns')
            ->has('tableActions', 2)
            ->has('tableBulkActions', 2)
            ->has('urls.create')
        );
});

it('lists the registered discount types for the type filter', function () {
    $this->get(route('panel.discounts.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('types', fn ($types) => collect($types)->pluck('value')->all() === [
                PercentageOff::class,
                FixedAmountOff::class,
                BuyXGetY::class,
            ])
        );
});

it('searches by name, handle and coupon', function () {
    Discount::factory()->create(['name' => 'Winter Sale', 'handle' => 'winter-sale', 'coupon' => 'WINTER']);
    Discount::factory()->create(['name' => 'Summer Sale', 'handle' => 'summer-sale', 'coupon' => 'SUMMER']);

    foreach (['Winter', 'winter-sale', 'WINTER'] as $term) {
        $this->get(route('panel.discounts.index', ['q' => $term]))
            ->assertInertia(fn (Assert $page) => $page->has('discounts.data', 1));
    }
});

it('filters by derived status', function () {
    Discount::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
    Discount::factory()->create(['starts_at' => now()->addWeek()]);
    Discount::factory()->create(['starts_at' => now()->subWeek(), 'ends_at' => now()->subDay()]);

    foreach (['active' => 1, 'scheduled' => 1, 'expired' => 1, 'nonsense' => 3] as $status => $expected) {
        $this->get(route('panel.discounts.index', ['status' => $status]))
            ->assertInertia(fn (Assert $page) => $page->has('discounts.data', $expected));
    }
});

it('filters by type', function () {
    Discount::factory()->create(['type' => PercentageOff::class]);
    Discount::factory()->count(2)->create(['type' => BuyXGetY::class]);

    $this->get(route('panel.discounts.index', ['type' => BuyXGetY::class]))
        ->assertInertia(fn (Assert $page) => $page->has('discounts.data', 2));
});

it('filters by whether a coupon is needed', function () {
    Discount::factory()->create(['coupon' => 'SAVE10']);
    Discount::factory()->count(2)->create(['coupon' => null]);

    $this->get(route('panel.discounts.index', ['redemption' => 'coupon']))
        ->assertInertia(fn (Assert $page) => $page->has('discounts.data', 1));

    $this->get(route('panel.discounts.index', ['redemption' => 'automatic']))
        ->assertInertia(fn (Assert $page) => $page->has('discounts.data', 2));
});

it('filters by channel and customer group availability', function () {
    // Both concerns attach every channel and group on create, so the filters
    // have to read the pivot's enabled flag rather than the row's existence.
    $channel = Channel::factory()->create();
    $group = CustomerGroup::factory()->create();

    $matching = Discount::factory()->create();
    $other = Discount::factory()->create();

    $matching->channels()->sync([$channel->id => ['enabled' => true]]);
    $matching->customerGroups()->sync([$group->id => ['enabled' => true, 'visible' => true]]);

    $other->channels()->sync([$channel->id => ['enabled' => false]]);
    $other->customerGroups()->sync([$group->id => ['enabled' => false, 'visible' => true]]);

    $this->get(route('panel.discounts.index', ['channel_id' => $channel->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('discounts.data', 1)
            ->where('discounts.data.0.id', $matching->id)
        );

    $this->get(route('panel.discounts.index', ['customer_group_id' => $group->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('discounts.data', 1)
            ->where('discounts.data.0.id', $matching->id)
        );
});

it('sorts by the allow-listed columns and falls back on unknown sorts', function () {
    $low = Discount::factory()->create(['name' => 'Alpha', 'priority' => 1]);
    $high = Discount::factory()->create(['name' => 'Zulu', 'priority' => 50]);

    $this->get(route('panel.discounts.index', ['sort' => 'name', 'direction' => 'desc']))
        ->assertInertia(fn (Assert $page) => $page->where('discounts.data.0.id', $high->id));

    // Unknown sorts fall back to priority ascending.
    $this->get(route('panel.discounts.index', ['sort' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page->where('discounts.data.0.id', $low->id));
});

it('reports the kpi strip', function () {
    Discount::factory()->create(['starts_at' => now()->subDay(), 'ends_at' => now()->addDays(3), 'uses' => 4]);
    Discount::factory()->create(['starts_at' => now()->addWeek(), 'uses' => 0]);

    $this->get(route('panel.discounts.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.active', 1)
            ->where('kpis.scheduled', 1)
            ->where('kpis.endingSoon', 1)
            ->where('kpis.redemptions', 4)
        );
});

it('is gated on the manage-discounts permission', function () {
    auth('staff')->logout();

    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff')->get(route('panel.discounts.index'))->assertForbidden();

    $staff->givePermissionTo('sales:manage-discounts');

    $this->actingAs($staff->fresh(), 'staff')->get(route('panel.discounts.index'))->assertOk();
});
