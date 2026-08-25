<?php

namespace Lunar\Filament\Support;

/**
 * Resolves panel-owned URLs for bridge tables and widgets.
 *
 * Bridge components link out to per-record pages that live in the consuming
 * panel — `lunarphp/admin` by default, or any Filament panel the consumer
 * builds. Each key under `lunar.filament.record_urls.{key}` says how to reach
 * that page:
 *
 *   null                        the bridge skips the link
 *   Page::class                 Page::getUrl([...$context, 'record' => $record])
 *   [Resource::class, 'edit']   Resource::getUrl('edit', [...$context, 'record' => $record])
 *   callable                    $resolver($record, $context)
 *
 * The class-string forms are the ones to reach for: config has to survive
 * `config:cache`, and a closure anywhere in the tree makes the whole of the
 * application's config non-serializable, not just this key. The callable form
 * stays supported for consumers already passing one, and for the rare resolver
 * that genuinely cannot be expressed as a class and a page name.
 */
class RecordUrls
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function for(string $key, mixed $record, array $context = []): ?string
    {
        $resolver = config('lunar.filament.record_urls.'.$key);

        $parameters = [...$context, 'record' => $record];

        if (is_string($resolver) && method_exists($resolver, 'getUrl')) {
            return $resolver::getUrl($parameters);
        }

        // Checked before is_callable: [Resource::class, 'edit'] would satisfy
        // is_callable if the resource happened to expose a static edit().
        if (is_array($resolver) && array_is_list($resolver) && count($resolver) === 2) {
            [$class, $page] = $resolver;

            if (is_string($class) && is_string($page) && method_exists($class, 'getUrl')) {
                return $class::getUrl($page, $parameters);
            }
        }

        if (is_callable($resolver)) {
            return $resolver($record, $context);
        }

        return null;
    }
}
