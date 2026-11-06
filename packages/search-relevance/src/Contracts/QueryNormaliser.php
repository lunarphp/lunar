<?php

namespace Lunar\SearchRelevance\Contracts;

interface QueryNormaliser
{
    public function normalise(string $query): string;

    /** One token mixing letters and digits, not a bare unit such as 20mm. Drives PartNumberRetrieval. */
    public function isPartNumber(string $query): bool;
}
