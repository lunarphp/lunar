<?php

use Lunar\Filament\Support\RecordUrls;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('returns null when no resolver is configured', function () {
    config()->set('lunar.filament.record_urls.order', null);

    expect(RecordUrls::for('order', (object) ['id' => 1]))->toBeNull();
});

it('invokes the configured callable resolver', function () {
    config()->set('lunar.filament.record_urls.order',
        fn ($record) => 'https://example.test/orders/'.$record->id,
    );

    expect(RecordUrls::for('order', (object) ['id' => 42]))
        ->toBe('https://example.test/orders/42');
});

it('returns null for unknown record keys', function () {
    expect(RecordUrls::for('not_a_real_key', (object) ['id' => 1]))->toBeNull();
});
