<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Exceptions;

use RuntimeException;

class UpgradeAbortedException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $remediation = null,
    ) {
        parent::__construct($message);
    }
}
