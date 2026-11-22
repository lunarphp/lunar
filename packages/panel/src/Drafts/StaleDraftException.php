<?php

namespace Lunar\Panel\Drafts;

use RuntimeException;

/**
 * The request was made against an older version of the staff member's draft
 * than the one stored: another tab saved in between. Nothing was written.
 */
class StaleDraftException extends RuntimeException
{
    public function __construct(public readonly int $currentVersion)
    {
        parent::__construct('The draft changed since this request was prepared.');
    }
}
