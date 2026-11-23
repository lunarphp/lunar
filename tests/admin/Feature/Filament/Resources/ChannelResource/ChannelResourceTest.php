<?php

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\EditChannel;
use Lunar\Admin\Filament\Resources\ChannelResource\Pages\ListChannels;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.channel');

beforeEach(function () {
    Language::factory()->create(['default' => true]);
});

it('deletes a channel that products and collections are attached to', function () {
    Channel::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => false]);
    $product = Product::factory()->create();
    Collection::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditChannel::class, ['record' => $channel->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Channel::find($channel->id))->toBeNull()
        ->and(DB::table(config('lunar.database.table_prefix').'channelables')->where('channel_id', $channel->id)->exists())->toBeFalse()
        ->and($product->channels()->count())->toBe(1);
});

it('does not delete the default channel', function () {
    $channel = Channel::factory()->create(['default' => true]);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditChannel::class, ['record' => $channel->getRouteKey()])
        ->callAction(DeleteAction::class)
        ->assertNotified(__('lunar-filament::channel.actions.delete.blocked'));

    expect(Channel::find($channel->id))->not->toBeNull();
});

it('bulk deletes channels in use while keeping the default channel', function () {
    $default = Channel::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => false]);
    Product::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(ListChannels::class)
        ->callTableBulkAction(DeleteBulkAction::class, [$default, $channel]);

    expect(Channel::find($channel->id))->toBeNull()
        ->and(Channel::find($default->id))->not->toBeNull()
        ->and(DB::table(config('lunar.database.table_prefix').'channelables')->where('channel_id', $channel->id)->exists())->toBeFalse();
});
