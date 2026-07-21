<?php

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
