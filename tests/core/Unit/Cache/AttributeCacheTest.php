<?php

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Cache\AttributeCache;
use Lunar\Core\Contracts\AttributeCache as AttributeCacheContract;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function attributeFor(string $handle): Attribute
{
    return Attribute::factory()->create([
        'handle' => $handle,
        'type' => FieldTypeEnum::Text->value,
    ]);
}

it('consults the cache store once however many lookups a request makes', function () {
    $attribute = attributeFor('description');

    // Spying the repository rather than counting queries: the test suite runs
    // on the array driver, where a cache read costs no query at all and the
    // assertion would hold with or without the memo. What matters is how often
    // the store is asked -- on the database driver, that is a round trip each
    // time.
    $store = Mockery::spy(Repository::class);
    $store->shouldReceive('rememberForever')->andReturnUsing(
        fn (string $key, Closure $callback): array => $callback(),
    );

    $cache = new AttributeCache($store, app(FieldTypeManifest::class));

    // The AsAttributeData cast resolves a handle and a field type per
    // attribute per model, so one page can ask for the maps a hundred times.
    for ($i = 0; $i < 25; $i++) {
        $cache->getIdForHandle('description');
        $cache->getHandleForId($attribute->id);
        $cache->getFieldTypeClassForId($attribute->id);
    }

    $store->shouldHaveReceived('rememberForever')->once();
});

it('still answers correctly from the memo', function () {
    $attribute = attributeFor('material');

    $cache = app(AttributeCacheContract::class);
    $cache->flush();

    // Prime, then read again: the second read comes from memory and must be
    // indistinguishable from the first.
    $cache->getIdForHandle('material');

    expect($cache->getIdForHandle('material'))->toBe($attribute->id)
        ->and($cache->getHandleForId($attribute->id))->toBe('material')
        ->and($cache->getFieldTypeClassForId($attribute->id))
        ->toBe(app(FieldTypeManifest::class)->getType(FieldTypeEnum::Text->value))
        ->and($cache->getIdForHandle('nope'))->toBeNull();
});

it('picks up a new attribute after a flush', function () {
    attributeFor('description');

    $cache = app(AttributeCacheContract::class);
    $cache->flush();

    // Prime the memo before the second attribute exists.
    expect($cache->getIdForHandle('warranty'))->toBeNull();

    $warranty = attributeFor('warranty');

    // Saving an attribute flushes through the observer, so the memo must not
    // survive it -- otherwise a panel edit would need a deploy to take effect.
    expect($cache->getIdForHandle('warranty'))->toBe($warranty->id);
});

it('drops the memo when flushed directly', function () {
    $attribute = attributeFor('finish');

    $cache = app(AttributeCacheContract::class);
    $cache->getIdForHandle('finish');

    $attribute->delete();
    $cache->flush();

    expect($cache->getIdForHandle('finish'))->toBeNull();
});
