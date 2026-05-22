<?php

use Lunar\Filament\Tables\Columns\TranslatedTextColumn;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('can be instantiated without the admin panel booted', function () {
    $column = TranslatedTextColumn::make('name');

    expect($column)->toBeInstanceOf(TranslatedTextColumn::class)
        ->and($column->getName())->toBe('name');
});
