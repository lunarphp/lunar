<?php

use Lunar\Core\Models\Currency;
use Lunar\Panel\Support\TimelineActivity;
use Lunar\Tests\Panel\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);

/**
 * @param  array<string, mixed>  $properties
 */
function makeActivity(string $description, array $properties): Activity
{
    $activity = new Activity;
    $activity->description = $description;
    $activity->event = $description;
    $activity->properties = $properties;

    return $activity;
}

it('reports only fields whose value actually changed', function () {
    // status is unchanged (logOnlyDirty false positive) and empty attribute_data
    // scaffolding ([] -> {"specs":[]}) is not a real edit; both are dropped.
    $activity = makeActivity('updated', [
        'old' => ['status' => 'published', 'attribute_data' => [], 'short_description' => ['en' => 'a']],
        'attributes' => ['status' => 'published', 'attribute_data' => ['specs' => []], 'short_description' => ['en' => 'b']],
    ]);

    expect(TimelineActivity::toArray($activity)['changes'])->toBe(['short_description']);
});

it('names the specific attribute handles that changed', function () {
    $activity = makeActivity('updated', [
        'old' => ['attribute_data' => ['specs' => [], 'material' => 'cotton']],
        'attributes' => ['attribute_data' => ['specs' => ['weight' => '200g'], 'material' => 'linen']],
    ]);

    expect(TimelineActivity::toArray($activity)['changes'])->toBe(['specs', 'material']);
});

it('treats empty attribute_data shapes as unchanged', function () {
    $activity = makeActivity('updated', [
        'old' => ['attribute_data' => []],
        'attributes' => ['attribute_data' => ['specs' => [], 'meta' => ['en' => '']]],
    ]);

    expect(TimelineActivity::toArray($activity)['changes'])->toBe([]);
});

it('ignores the updated_at timestamp', function () {
    $activity = makeActivity('updated', [
        'old' => ['updated_at' => '2020-01-01', 'name' => 'a'],
        'attributes' => ['updated_at' => '2021-01-01', 'name' => 'b'],
    ]);

    expect(TimelineActivity::toArray($activity)['changes'])->toBe(['name']);
});

it('reports no changed fields for create and delete events', function () {
    $created = makeActivity('created', ['attributes' => ['id' => 1, 'name' => 'x']]);
    $deleted = makeActivity('deleted', ['old' => ['id' => 1, 'name' => 'x']]);

    expect(TimelineActivity::toArray($created)['changes'])->toBe([])
        ->and(TimelineActivity::toArray($deleted)['changes'])->toBe([]);
});

/**
 * @param  array<string, mixed>  $properties
 */
function makeOrderActivity(string $event, string $description, array $properties = []): Activity
{
    $activity = new Activity;
    $activity->event = $event;
    $activity->description = $description;
    $activity->properties = $properties;

    return $activity;
}

it('labels each order event with a translated description and detail', function (string $event, string $description, array $properties, string $label, ?string $detail) {
    $currency = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);

    $entry = TimelineActivity::forOrder(makeOrderActivity($event, $description, $properties), $currency);

    expect($entry['description'])->toBe($label)
        ->and($entry['detail'])->toBe($detail);
})->with([
    'order created' => ['created', 'created', [], 'Order created', null],
    'order updated' => ['updated', 'updated', [], 'Order updated', null],
    'order cancelled with a reason and note' => ['order-cancelled', 'order-cancelled', ['reason' => 'customer', 'note' => 'Called to cancel'], 'Order cancelled: Customer changed/cancelled order', 'Called to cancel'],
    'order cancelled without a reason' => ['order-cancelled', 'order-cancelled', ['reason' => null, 'note' => null], 'Order cancelled', null],
    'order closed' => ['order-closed', 'order-closed', [], 'Order closed', null],
    'order reopened' => ['order-reopened', 'order-reopened', [], 'Order reopened', null],
    'email notification' => ['email-notification', 'email-notification', ['notification' => 'Order shipped', 'email' => 'ada@example.com', 'message' => 'On its way'], 'Notification sent: Order shipped (ada@example.com)', 'On its way'],
    'payment authorized' => ['intent', 'created', ['amount' => 12000, 'type' => 'intent', 'notes' => ''], 'Payment authorized: £120.00', null],
    'payment captured' => ['capture', 'created', ['amount' => 12000, 'type' => 'capture', 'notes' => 'Paid by bank transfer'], 'Payment captured: £120.00', 'Paid by bank transfer'],
    'refund issued' => ['refund', 'created', ['amount' => 2500, 'type' => 'refund', 'notes' => 'Damaged item'], 'Refund issued: £25.00', 'Damaged item'],
    'fulfilment state change' => ['fulfilment-update', 'fulfilment-update', ['fulfilment_id' => 7, 'reference' => 'F-1', 'type' => 'state', 'from' => 'pending', 'to' => 'shipped'], 'Fulfilment F-1: Shipped', null],
    'fulfilment held' => ['fulfilment-update', 'fulfilment-update', ['fulfilment_id' => 7, 'reference' => null, 'type' => 'held', 'reason' => 'awaiting-payment'], 'Fulfilment #7 placed on hold', 'Awaiting payment'],
    'fulfilment released' => ['fulfilment-update', 'fulfilment-update', ['fulfilment_id' => 7, 'reference' => 'F-1', 'type' => 'released'], 'Fulfilment F-1 released from hold', null],
    'shipping address updated' => ['order-address-update', 'order-address-update', ['type' => 'shipping'], 'Shipping address updated', null],
    'comment' => ['comment', 'comment', ['content' => 'Customer called'], 'Comment added', 'Customer called'],
    'tags updated' => ['tags-update', 'tags-update', ['added' => ['VIP'], 'removed' => []], 'Tags updated', null],
]);

it('humanises an order event it does not know', function () {
    $entry = TimelineActivity::forOrder(makeOrderActivity('loyalty-points_awarded', 'loyalty-points_awarded'), null);

    expect($entry['description'])->toBe('Loyalty points awarded')
        ->and($entry['detail'])->toBeNull();
});
