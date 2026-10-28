<?php

namespace Lunar\Core\Manifests;

use Closure;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Exceptions\UnknownStepException;

/**
 * One ordered step list: core's seed plus the changes registered against it.
 * Changes are recorded in registration order and applied when the list is read.
 */
class StepSequence
{
    /**
     * @var list<array{0: string, 1: ?class-string, 2: class-string}>
     */
    protected array $changes = [];

    /**
     * @var list<class-string>|null
     */
    protected ?array $resolved = null;

    /**
     * @param  list<class-string>  $seed
     * @param  Closure(): void  $beforeRead  runs before the list is first resolved
     */
    public function __construct(
        protected StepList $list,
        protected array $seed,
        protected Closure $beforeRead,
    ) {}

    /**
     * @param  class-string  $step
     */
    public function append(string $step): static
    {
        return $this->record('append', null, $step);
    }

    /**
     * @param  class-string  $step
     */
    public function prepend(string $step): static
    {
        return $this->record('prepend', null, $step);
    }

    /**
     * @param  class-string  $anchor
     * @param  class-string  $step
     */
    public function before(string $anchor, string $step): static
    {
        return $this->record('before', $anchor, $step);
    }

    /**
     * @param  class-string  $anchor
     * @param  class-string  $step
     */
    public function after(string $anchor, string $step): static
    {
        return $this->record('after', $anchor, $step);
    }

    /**
     * @param  class-string  $step
     */
    public function remove(string $step): static
    {
        return $this->record('remove', null, $step);
    }

    /**
     * @param  class-string  $existing
     * @param  class-string  $step
     */
    public function replace(string $existing, string $step): static
    {
        return $this->record('replace', $existing, $step);
    }

    /**
     * The resolved list, in the order the steps run.
     *
     * @return list<class-string>
     *
     * @throws UnknownStepException when a change is anchored to a step that is not in the list
     */
    public function all(): array
    {
        if ($this->resolved === null) {
            ($this->beforeRead)();
        }

        return $this->resolved ??= $this->resolve();
    }

    /**
     * @param  class-string|null  $anchor
     * @param  class-string  $step
     */
    protected function record(string $change, ?string $anchor, string $step): static
    {
        $this->changes[] = [$change, $anchor, $step];
        $this->resolved = null;

        return $this;
    }

    /**
     * @return list<class-string>
     */
    protected function resolve(): array
    {
        $steps = $this->seed;

        // Steps already placed after each anchor, so a later after() lands behind them.
        $placedAfter = [];

        foreach ($this->changes as [$change, $anchor, $step]) {
            if ($change === 'remove') {
                $steps = array_values(array_filter($steps, fn (string $existing): bool => $existing !== $step));

                continue;
            }

            if ($change === 'replace') {
                $steps[$this->positionOf($steps, $anchor, $step)] = $step;
                $steps = array_values(array_unique($steps));

                continue;
            }

            if (in_array($step, $steps, true)) {
                continue;
            }

            if ($change === 'append') {
                $steps[] = $step;
            } elseif ($change === 'prepend') {
                array_unshift($steps, $step);
            } elseif ($change === 'before') {
                array_splice($steps, $this->positionOf($steps, $anchor, $step), 0, [$step]);
            } else {
                $position = $this->positionOf($steps, $anchor, $step) + 1;

                while (isset($steps[$position]) && in_array($steps[$position], $placedAfter[$anchor] ?? [], true)) {
                    $position++;
                }

                array_splice($steps, $position, 0, [$step]);
                $placedAfter[$anchor][] = $step;
            }
        }

        return $steps;
    }

    /**
     * @param  list<class-string>  $steps
     * @param  class-string  $anchor
     * @param  class-string  $step
     */
    protected function positionOf(array $steps, string $anchor, string $step): int
    {
        $position = array_search($anchor, $steps, true);

        if ($position === false) {
            throw new UnknownStepException($this->list, $anchor, $step);
        }

        return $position;
    }
}
