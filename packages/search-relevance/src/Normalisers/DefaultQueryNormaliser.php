<?php

namespace Lunar\SearchRelevance\Normalisers;

use Lunar\SearchRelevance\Contracts\QueryNormaliser;

class DefaultQueryNormaliser implements QueryNormaliser
{
    protected const UNITS = 'mm|cm|m|v|a|w|kw|amp';

    public function normalise(string $query): string
    {
        $q = mb_strtolower(trim($query));

        // Keep the characters common in part numbers: . - /
        $q = preg_replace('/[^\p{L}\p{N}\s.\-\/]/u', ' ', $q);

        // "20 mm" becomes "20mm"
        $q = preg_replace('/(\d)\s+('.static::UNITS.')\b/u', '$1$2', $q);

        $q = preg_replace('/\s+/u', ' ', trim($q));

        if ($this->isPartNumber($q)) {
            return $q; // never stem or reshape a part number
        }

        // Light plural stemming so "cable ties" and "cable tie" pool their data
        $q = preg_replace('/\b(\p{L}{2,})ies\b/u', '$1y', $q);
        $q = preg_replace('/\b(\p{L}+(?:x|ch|sh))es\b/u', '$1', $q);
        $q = preg_replace('/\b(\p{L}{2,}[^s\W])s\b/u', '$1', $q);

        return preg_replace('/\s+/u', ' ', trim($q));
    }

    public function isPartNumber(string $query): bool
    {
        return static::looksLikePartNumber($query);
    }

    /** One token mixing letters and digits (hyphens, dots and slashes allowed), e.g. HAG-MB-32A or hagmb32. */
    public static function looksLikePartNumber(string $query): bool
    {
        $q = trim($query);

        return $q !== ''
            && ! preg_match('/\s/u', $q)
            && ! preg_match('/^\d+(\.\d+)?('.static::UNITS.')$/i', $q) // a bare size, not a part number
            && preg_match('/^[a-z0-9][a-z0-9.\-\/]*$/i', $q)
            && preg_match('/\p{L}/u', $q)
            && preg_match('/\d/', $q);
    }
}
