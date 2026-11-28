<?php

use Lunar\Tests\Admin\TestCase;

uses(TestCase::class)
    ->group('lunar.panel.helpers');

function useDatabaseDriver(string $driver): void
{
    $connection = config('database.default');

    config(["database.connections.{$connection}.driver" => $driver]);
}

it('builds a DATE_FORMAT expression on mysql', function (string $format, string $expected) {
    useDatabaseDriver('mysql');

    expect(db_date('placed_at', $format, 'alias'))->toBe($expected);
})->with([
    '%M' => ['%M', "DATE_FORMAT(placed_at, '%M') as alias"],
    '%Y' => ['%Y', "DATE_FORMAT(placed_at, '%Y') as alias"],
    '%Y%m' => ['%Y%m', "DATE_FORMAT(placed_at, '%Y%m') as alias"],
    '%Y-%m' => ['%Y-%m', "DATE_FORMAT(placed_at, '%Y-%m') as alias"],
    '%M %Y' => ['%M %Y', "DATE_FORMAT(placed_at, '%M %Y') as alias"],
]);

it('translates mysql format codes to TO_CHAR patterns on pgsql', function (string $format, string $expected) {
    useDatabaseDriver('pgsql');

    expect(db_date('placed_at', $format, 'alias'))->toBe($expected);
})->with([
    '%M' => ['%M', "TO_CHAR(placed_at :: DATE, 'FMMonth') as alias"],
    '%Y' => ['%Y', "TO_CHAR(placed_at :: DATE, 'YYYY') as alias"],
    '%Y%m' => ['%Y%m', "TO_CHAR(placed_at :: DATE, 'YYYYMM') as alias"],
    '%Y-%m' => ['%Y-%m', "TO_CHAR(placed_at :: DATE, 'YYYY-MM') as alias"],
    '%M %Y' => ['%M %Y', "TO_CHAR(placed_at :: DATE, 'FMMonth YYYY') as alias"],
    '%Y-%m-%d' => ['%Y-%m-%d', "TO_CHAR(placed_at :: DATE, 'YYYY-MM-DD') as alias"],
]);

it('builds a strftime expression on sqlite', function () {
    useDatabaseDriver('sqlite');

    expect(db_date('placed_at', '%Y-%m', 'alias'))->toBe("strftime('%Y-%m', placed_at) as alias");
});

it('omits the alias when none is given', function () {
    useDatabaseDriver('pgsql');

    expect(db_date('placed_at', '%Y-%m'))->toBe("TO_CHAR(placed_at :: DATE, 'YYYY-MM')");
});
