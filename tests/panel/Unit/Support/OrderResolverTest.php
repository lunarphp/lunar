<?php

use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * The resolver orders any list whose items expose a key and a Position.
 * Tests use plain arrays with those two keys.
 *
 * @param  array<int, array{key: string, position: Position}>  $items
 * @return string[]
 */
function resolveKeys(array $items): array
{
    $ordered = (new OrderResolver)->sort(
        $items,
        fn (array $item): string => $item['key'],
        fn (array $item): Position => $item['position'],
    );

    return array_map(fn (array $item): string => $item['key'], $ordered);
}

/** @return array{key: string, position: Position} */
function item(string $key, Position $position): array
{
    return ['key' => $key, 'position' => $position];
}

it('orders by numeric priority, lowest first', function () {
    $keys = resolveKeys([
        item('c', Position::priority(30)),
        item('a', Position::priority(10)),
        item('b', Position::priority(20)),
    ]);

    expect($keys)->toBe(['a', 'b', 'c']);
});

it('preserves registration order for equal priority', function () {
    $keys = resolveKeys([
        item('first', Position::priority(50)),
        item('second', Position::priority(50)),
        item('third', Position::priority(50)),
    ]);

    expect($keys)->toBe(['first', 'second', 'third']);
});

it('places first() at the front and last() at the back regardless of priority', function () {
    $keys = resolveKeys([
        item('mid', Position::priority(50)),
        item('end', Position::last()),
        item('start', Position::first()),
    ]);

    expect($keys)->toBe(['start', 'mid', 'end']);
});

it('places an after() entry immediately after its target', function () {
    $keys = resolveKeys([
        item('a', Position::priority(10)),
        item('b', Position::priority(20)),
        item('c', Position::priority(30)),
        item('injected', Position::after('a')),
    ]);

    expect($keys)->toBe(['a', 'injected', 'b', 'c']);
});

it('places a before() entry immediately before its target', function () {
    $keys = resolveKeys([
        item('a', Position::priority(10)),
        item('b', Position::priority(20)),
        item('c', Position::priority(30)),
        item('injected', Position::before('b')),
    ]);

    expect($keys)->toBe(['a', 'injected', 'b', 'c']);
});

it('resolves anchor chains (an anchored entry targeting another anchored entry)', function () {
    $keys = resolveKeys([
        item('a', Position::priority(10)),
        item('b', Position::priority(20)),
        item('x', Position::after('a')),
        item('y', Position::after('x')),
    ]);

    expect($keys)->toBe(['a', 'x', 'y', 'b']);
});

it('orders multiple entries anchored to the same target by their priority', function () {
    $keys = resolveKeys([
        item('a', Position::priority(10)),
        item('b', Position::priority(20)),
        item('high', new Position('after', 'a', priority: 10)),
        item('low', new Position('after', 'a', priority: 90)),
    ]);

    expect($keys)->toBe(['a', 'high', 'low', 'b']);
});

it('falls back to priority position when an anchor target does not exist', function () {
    $keys = resolveKeys([
        item('a', Position::priority(10)),
        item('b', Position::priority(30)),
        item('orphan', new Position('after', 'nope', priority: 20)),
    ]);

    expect($keys)->toBe(['a', 'orphan', 'b']);
});

it('does not loop or throw on circular anchors, falling back to priority', function () {
    $keys = resolveKeys([
        item('a', new Position('after', 'b', priority: 10)),
        item('b', new Position('after', 'a', priority: 20)),
    ]);

    expect($keys)->toBe(['a', 'b']);
});
