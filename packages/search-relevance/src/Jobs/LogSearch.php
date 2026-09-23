<?php

namespace Lunar\SearchRelevance\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Lunar\SearchRelevance\Models\SearchQuery;

class LogSearch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /** @param array<string, mixed> $attributes Includes the ulid generated at request time. */
    public function __construct(public array $attributes) {}

    public function handle(): void
    {
        SearchQuery::query()->create($this->attributes);
    }
}
