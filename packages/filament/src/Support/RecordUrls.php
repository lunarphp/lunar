<?php

namespace Lunar\Filament\Support;

/**
 * Resolves panel-owned URLs for bridge tables and widgets.
 *
 * Bridge components link out to per-record pages that live in the consuming
 * panel — `lunarphp/admin` by default, or any Filament panel the consumer
 * builds. Each key under `lunar.filament.record_urls.{key}` is a closure
 * receiving the record and returning a URL, or null when the bridge should
 * skip the link.
 */
class RecordUrls
{
    public static function for(string $key, mixed $record, array $context = []): ?string
    {
        $resolver = config('lunar.filament.record_urls.'.$key);

        if (! is_callable($resolver)) {
            return null;
        }

        return $resolver($record, $context);
    }
}
