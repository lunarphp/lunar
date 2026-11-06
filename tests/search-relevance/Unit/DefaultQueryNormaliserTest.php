<?php

use Lunar\SearchRelevance\Normalisers\DefaultQueryNormaliser;

uses()->group('search-relevance');

beforeEach(function () {
    $this->normaliser = new DefaultQueryNormaliser;
});

it('lowercases, trims and collapses whitespace', function () {
    expect($this->normaliser->normalise('  Cable   Gland '))->toBe('cable gland');
});

it('joins a number to its unit', function () {
    expect($this->normaliser->normalise('20 mm gland'))->toBe('20mm gland')
        ->and($this->normaliser->normalise('32 A breaker'))->toBe('32a breaker');
});

it('strips punctuation but keeps part-number separators', function () {
    expect($this->normaliser->normalise('cable, ties!'))->toBe('cable tie')
        ->and($this->normaliser->normalise('HAG-MB-32A'))->toBe('hag-mb-32a')
        ->and($this->normaliser->normalise('AB/12.5'))->toBe('ab/12.5');
});

it('applies light plural stemming', function () {
    expect($this->normaliser->normalise('cable ties'))->toBe('cable tie')
        ->and($this->normaliser->normalise('boxes'))->toBe('box')
        ->and($this->normaliser->normalise('switches'))->toBe('switch')
        ->and($this->normaliser->normalise('glands'))->toBe('gland')
        ->and($this->normaliser->normalise('glass'))->toBe('glass');
});

it('never stems a part number', function () {
    expect($this->normaliser->normalise('MCB32S'))->toBe('mcb32s');
});

it('classifies part numbers', function (string $query, bool $expected) {
    expect($this->normaliser->isPartNumber($query))->toBe($expected);
})->with([
    ['HAG-MB-32A', true],
    ['hagmb32', true],
    ['ab/12.5', true],
    ['20mm', false],
    ['2.5mm', false],
    ['32a', false],
    ['cable-gland', false],
    ['cable tie', false],
    ['12345', false],
    ['', false],
    ['hag mb32', false],
]);
