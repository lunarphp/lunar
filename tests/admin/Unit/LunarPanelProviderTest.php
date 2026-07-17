<?php

use Lunar\Admin\Support\RecordUrlResolvers;
use Lunar\Tests\Admin\Unit\Filament\TestCase;

uses(TestCase::class)
    ->group('lunar.admin');

it('registers serializable record URL resolvers', function (): void {
    $resolvers = [
        'lunar.filament.record_urls.order' => [RecordUrlResolvers::class, 'order'],
        'lunar.filament.record_urls.product_variant' => [RecordUrlResolvers::class, 'productVariant'],
        'lunar.filament.record_urls.collection_edit' => [RecordUrlResolvers::class, 'collectionEdit'],
    ];

    foreach ($resolvers as $key => $resolver) {
        expect(config($key))
            ->toBe($resolver)
            ->and(is_callable($resolver))->toBeTrue();
    }
});
