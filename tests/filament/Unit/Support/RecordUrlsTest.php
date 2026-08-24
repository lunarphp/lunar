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

it('resolves a page class string', function () {
    config()->set('lunar.filament.record_urls.order', FakeRecordPage::class);

    expect(RecordUrls::for('order', (object) ['id' => 7], ['tenant' => 'uk']))
        ->toBe('page:{"tenant":"uk","record":7}');
});

it('resolves a resource class string with a page name', function () {
    config()->set('lunar.filament.record_urls.product_variant', [FakeRecordResource::class, 'edit']);

    expect(RecordUrls::for('product_variant', (object) ['id' => 9]))
        ->toBe('resource:edit:{"record":9}');
});

it('keeps working when the configured value is nonsense', function () {
    config()->set('lunar.filament.record_urls.order', ['not', 'a', 'pair']);

    expect(RecordUrls::for('order', (object) ['id' => 1]))->toBeNull();

    config()->set('lunar.filament.record_urls.order', 'Not\\A\\Real\\Class');

    expect(RecordUrls::for('order', (object) ['id' => 1]))->toBeNull();
});

it('serialises to config that survives config:cache', function () {
    // The whole point: var_export is what config:cache uses, and it throws on
    // a closure anywhere in the tree.
    config()->set('lunar.filament.record_urls', [
        'order' => FakeRecordPage::class,
        'product_variant' => [FakeRecordResource::class, 'edit'],
        'collection_edit' => null,
    ]);

    expect(fn () => var_export(config('lunar.filament.record_urls'), true))
        ->not->toThrow(Throwable::class);
});

class FakeRecordPage
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function getUrl(array $parameters = []): string
    {
        return 'page:'.json_encode(recordUrlIds($parameters));
    }
}

class FakeRecordResource
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function getUrl(string $name = 'index', array $parameters = []): string
    {
        return 'resource:'.$name.':'.json_encode(recordUrlIds($parameters));
    }
}

/**
 * @param  array<string, mixed>  $parameters
 * @return array<string, mixed>
 */
function recordUrlIds(array $parameters): array
{
    return array_map(
        fn (mixed $value): mixed => is_object($value) ? $value->id : $value,
        $parameters,
    );
}
