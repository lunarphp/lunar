<?php

namespace Lunar\Api\Support;

use Lunar\Api\Models\ApiKey;

/** A freshly issued key and the plaintext token that is never stored. */
final readonly class NewApiKey
{
    public function __construct(
        public ApiKey $key,
        public string $plainTextToken,
    ) {}
}
