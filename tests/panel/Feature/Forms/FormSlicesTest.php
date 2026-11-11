<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Forms\FormSlices;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Panel\Fixtures\Drafts\MemoSlice;
use Lunar\Tests\Panel\Fixtures\Forms\ChannelMemoSlice;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    MemoSlice::$committed = [];
    ChannelMemoSlice::$memos = [];
    ChannelMemoSlice::$commits = [];

    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    app(PanelManager::class)
        ->formSlice(MemoSlice::class)
        ->formSlice(ChannelMemoSlice::class);
});

it('composes slice rules under sometimes so an unbound namespace passes', function () {
    $rules = app(FormSlices::class)->rules(Channel::class);

    expect($rules)->toBe(['notes:memo' => ['sometimes', 'required', 'string', 'max:20']]);
});

it('seeds a settings edit page with the record slice values', function () {
    $channel = Channel::factory()->create();
    ChannelMemoSlice::$memos[$channel->id] = 'hello';

    $this->get(route('panel.settings.channels.edit', $channel))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('formSliceValues.notes:memo', 'hello'));
});

it('seeds a create page with the slice values of a fresh record', function () {
    $this->get(route('panel.customers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('formSliceValues.notes:memo', null));
});

it('commits a bound slice after a settings update inside the same transaction', function () {
    $channel = Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->put(route('panel.settings.channels.update', $channel), [
        'name' => 'Retail Store',
        'notes:memo' => 'updated',
    ])->assertRedirect(route('panel.settings.channels.index'));

    expect($channel->refresh()->name)->toBe('Retail Store')
        ->and(ChannelMemoSlice::$memos[$channel->id])->toBe('updated')
        ->and(ChannelMemoSlice::$commits)->toBe([['memo' => 'updated']]);
});

it('leaves an unbound namespace alone on a plain form', function () {
    $channel = Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->put(route('panel.settings.channels.update', $channel), ['name' => 'Retail Store'])
        ->assertRedirect(route('panel.settings.channels.index'));

    expect($channel->refresh()->name)->toBe('Retail Store')
        ->and(ChannelMemoSlice::$commits)->toBe([]);
});

it('validates a bound slice with the form in one round, under the prefixed key', function () {
    $channel = Channel::factory()->create(['default' => false]);

    $this->from(route('panel.settings.channels.edit', $channel))
        ->put(route('panel.settings.channels.update', $channel), [
            'name' => '',
            'notes:memo' => str_repeat('x', 21),
        ])
        ->assertRedirect(route('panel.settings.channels.edit', $channel))
        ->assertSessionHasErrors(['name', 'notes:memo']);

    expect(ChannelMemoSlice::$commits)->toBe([]);
});

it('rolls the form action back when a slice commit fails', function () {
    $channel = Channel::factory()->create(['name' => 'Retail', 'default' => false]);

    $this->withoutExceptionHandling();

    try {
        $this->put(route('panel.settings.channels.update', $channel), ['name' => 'Renamed', 'notes:memo' => 'boom']);
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Channel memo slice commit failed.');
    }

    expect($channel->refresh()->name)->toBe('Retail');
});

it('commits a bound slice against the record a create form produces', function () {
    $this->post(route('panel.customers.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'notes:memo' => 'from create',
    ])->assertRedirect();

    $customer = Customer::sole();

    expect($customer->meta['memo'])->toBe('from create')
        ->and(MemoSlice::$committed)->toBe(['slice']);
});

it('creates a record untouched when no slice keys are posted', function () {
    $this->post(route('panel.customers.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
    ])->assertRedirect();

    expect(Customer::sole()->meta)->toBeNull()
        ->and(MemoSlice::$committed)->toBe([]);
});
