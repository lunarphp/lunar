<?php

namespace Lunar\Core\Cache;

use Closure;
use Lunar\Core\Contracts\CacheDependencies as CacheDependenciesContract;

class CacheDependencies implements CacheDependenciesContract
{
    /** @var array<string, array<int, string>|Closure> */
    protected array $graphs = [];

    public function define(string $name, array|Closure $definition): void
    {
        $this->graphs[$name] = $definition;
    }

    public function get(string $name): array|Closure|null
    {
        return $this->graphs[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->graphs[$name]);
    }

    public function flush(): void
    {
        $this->graphs = [];
    }
}
