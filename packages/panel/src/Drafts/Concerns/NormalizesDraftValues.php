<?php

namespace Lunar\Panel\Drafts\Concerns;

/**
 * Value canonicalisation shared by draft resources and slices, so a drafted
 * value compares equal to the stored one it represents.
 */
trait NormalizesDraftValues
{
    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    protected function sortedIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        sort($ids);

        return $ids;
    }

    /**
     * Attribute values arrive in whatever shape their field type stores;
     * translated-text maps get key-sorted with blank entries dropped so
     * equality against the stored value holds. Sequential arrays keep their
     * order, as do keyed list values.
     */
    protected function normalizeAttributeValue(mixed $value, ?string $token = null): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return $value;
        }

        if ($token === 'list') {
            return array_map(fn (mixed $item) => is_string($item) ? $item : (string) $item, $value);
        }

        return $this->translationMap($value);
    }

    /**
     * Normalise a `{locale: text}` translation map so equality against the
     * stored value holds: empty values are dropped and keys are sorted.
     *
     * @param  array<string, mixed>  $map
     * @return array<string, string>
     */
    protected function translationMap(array $map): array
    {
        $map = array_filter(
            array_map(fn (mixed $value) => is_string($value) ? $value : (string) $value, $map),
            fn (string $value) => $value !== '',
        );

        ksort($map);

        return $map;
    }

    /**
     * Re-key a prefixed map (`attribute:handle` => value) by the bare field
     * name, for slices built on helpers that speak in full draft keys.
     *
     * @template TValue
     *
     * @param  array<string, TValue>  $keyed
     * @return array<string, TValue>
     */
    protected function stripPrefix(array $keyed, string $prefix): array
    {
        $stripped = [];

        foreach ($keyed as $key => $value) {
            $stripped[str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key] = $value;
        }

        return $stripped;
    }

    /**
     * Strip a prefix from a list of full draft keys.
     *
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    protected function stripPrefixFromList(array $keys, string $prefix): array
    {
        return array_values(array_map(
            fn (string $key) => str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key,
            $keys,
        ));
    }
}
