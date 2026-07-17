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

it('can reload record URL resolver configuration after export', function (): void {
    $cachedConfigurationPath = tempnam(sys_get_temp_dir(), 'lunar-config-');

    if ($cachedConfigurationPath === false) {
        throw new RuntimeException('Unable to create a cached configuration file.');
    }

    try {
        file_put_contents(
            $cachedConfigurationPath,
            '<?php return '.var_export(config('lunar.filament.record_urls'), true).';',
        );

        expect(require $cachedConfigurationPath)
            ->toBe(config('lunar.filament.record_urls'));
    } finally {
        unlink($cachedConfigurationPath);
    }
});
