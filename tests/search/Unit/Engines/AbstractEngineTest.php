<?php

use Lunar\Search\Engines\AbstractEngine;

uses(\Lunar\Tests\Search\TestCase::class);

test('can set and get the query', function () {
    $engine = new class extends AbstractEngine
    {
        public function get(): \Illuminate\Support\Collection
        {
            return collect();
        }
    };

    expect($engine->getQuery())->toBeEmpty();
})->group('search');
