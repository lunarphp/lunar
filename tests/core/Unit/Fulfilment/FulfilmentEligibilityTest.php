<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Fulfilment\CancelFulfilment;
use Lunar\Core\Actions\Fulfilment\CreateFulfilment;
use Lunar\Core\Actions\Fulfilment\MergeFulfilments;
use Lunar\Core\Actions\Fulfilment\ReturnFulfilment;
use Lunar\Core\Actions\Fulfilment\ShipFulfilment;
use Lunar\Core\Actions\Fulfilment\SplitFulfilment;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true]);
});

function eligibilityOrderLine(int $quantity = 10): array
{
    $order = Order::factory()->create();
    $line = OrderLine::factory()->create(['order_id' => $order->id, 'type' => 'physical', 'quantity' => $quantity]);

    return [$order, $line];
}

test('ship/cancel/return eligibility follows the fulfilment state graph', function () {
    $pending = Fulfilment::factory()->create(['state' => 'pending']);
    $shipped = Fulfilment::factory()->create(['state' => 'shipped']);

    expect(ShipFulfilment::canRun($pending))->toBeTrue()
        ->and(ShipFulfilment::canRun($shipped))->toBeFalse()
        ->and(CancelFulfilment::canRun($pending))->toBeTrue()
        ->and(CancelFulfilment::canRun($shipped))->toBeFalse()
        ->and(ReturnFulfilment::canRun($pending))->toBeFalse()
        ->and(ReturnFulfilment::canRun($shipped))->toBeTrue();
});

test('split eligibility is limited to pre-ship fulfilments', function () {
    expect(SplitFulfilment::canRun(Fulfilment::factory()->create(['state' => 'pending'])))->toBeTrue()
        ->and(SplitFulfilment::canRun(Fulfilment::factory()->create(['state' => 'in-progress'])))->toBeTrue()
        ->and(SplitFulfilment::canRun(Fulfilment::factory()->create(['state' => 'shipped'])))->toBeFalse();
});

test('merge eligibility requires pre-ship parcels on the same order', function () {
    [$order, $line] = eligibilityOrderLine(10);
    $target = $order->createFulfilment([$line->id => 3]);
    $source = $order->createFulfilment([$line->id => 2]);

    expect(MergeFulfilments::canRun($target, Fulfilment::whereKey($source->id)->get()))->toBeTrue();
});

test('merge eligibility is false for an empty source set', function () {
    [$order, $line] = eligibilityOrderLine(10);
    $target = $order->createFulfilment([$line->id => 3]);

    expect(MergeFulfilments::canRun($target, Fulfilment::whereKey(-1)->get()))->toBeFalse();
});

test('merge eligibility is false when the target is among the sources', function () {
    [$order, $line] = eligibilityOrderLine(10);
    $target = $order->createFulfilment([$line->id => 3]);

    expect(MergeFulfilments::canRun($target, Fulfilment::whereKey($target->id)->get()))->toBeFalse();
});

test('merge eligibility is false across different orders', function () {
    [$orderA, $lineA] = eligibilityOrderLine(10);
    [$orderB, $lineB] = eligibilityOrderLine(10);
    $target = $orderA->createFulfilment([$lineA->id => 3]);
    $source = $orderB->createFulfilment([$lineB->id => 2]);

    expect(MergeFulfilments::canRun($target, Fulfilment::whereKey($source->id)->get()))->toBeFalse();
});

test('merge eligibility is false when a source has shipped', function () {
    [$order, $line] = eligibilityOrderLine(10);
    $target = $order->createFulfilment([$line->id => 3]);
    $source = $order->createFulfilment([$line->id => 2]);
    $source->ship();

    expect(MergeFulfilments::canRun($target, Fulfilment::whereKey($source->id)->get()))->toBeFalse();
});

test('create eligibility reflects outstanding quantity', function () {
    [$order, $line] = eligibilityOrderLine(5);

    expect(CreateFulfilment::canRun($order))->toBeTrue();

    $order->createFulfilment([$line->id => 5]);

    expect(CreateFulfilment::canRun($order->refresh()))->toBeFalse();
});
