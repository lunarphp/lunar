<?php

use Lunar\Search\Engines\AbstractEngine;

uses(\Lunar\Tests\Search\TestCase::class)->group('search');

test('can set and get the query', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): \Illuminate\Support\Collection
        {
            return collect();
        }
    };

    expect($engine->getQuery())->toBeEmpty();
    $engine->query('Foobar');
    expect($engine->getQuery())->toBe('Foobar');
});

test('can set and get filters', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): \Illuminate\Support\Collection
        {
            return collect();
        }
    };

    $filters = [
        'color' => ['Red'],
        'size' => 'Small',
    ];

    $engine->filter($filters);

    expect($engine->getFilters())->toEqual($filters);

    $engine->addFilter('color', 'Blue');

    expect($engine->getFilters()['color'])->toEqual('Blue');
});
