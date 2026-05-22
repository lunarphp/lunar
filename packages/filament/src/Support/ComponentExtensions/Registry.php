<?php

namespace Lunar\Filament\Support\ComponentExtensions;

class Registry
{
    /** @var array<class-string, array<int, object>> */
    protected array $extensions = [];

    /**
     * Register one or more extension instances against bridge target classes.
     *
     * Accepts the same shape as `LunarPanel::extensions([…])`:
     *   [TargetClass::class => [new ExtensionA, new ExtensionB]]
     * or
     *   [TargetClass::class => new ExtensionA]
     */
    public function register(array $extensions): static
    {
        foreach ($extensions as $target => $instances) {
            $instances = is_array($instances) ? $instances : [$instances];

            $this->extensions[$target] ??= [];

            foreach ($instances as $instance) {
                $this->extensions[$target][] = $instance;
            }
        }

        return $this;
    }

    /**
     * @return array<class-string, array<int, object>>
     */
    public function all(): array
    {
        return $this->extensions;
    }

    /**
     * @return array<int, object>
     */
    public function for(string $target): array
    {
        return $this->extensions[$target] ?? [];
    }

    /**
     * Invoke `$hookName(...$args)` on every extension registered against `$target`.
     * The first non-null return value wins; otherwise returns null.
     */
    public function callHook(string $target, ?object $caller, string $hookName, mixed ...$args): mixed
    {
        $result = null;

        foreach ($this->for($target) as $extension) {
            if (! method_exists($extension, $hookName)) {
                continue;
            }

            $value = $extension->{$hookName}($caller, ...$args);

            if ($value !== null) {
                $result = $value;
            }
        }

        return $result;
    }
}
