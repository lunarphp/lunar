<?php

namespace Lunar\Panel\Search;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

/**
 * Resolves the registered search commands into the ordered, visible list shared
 * to the frontend, where the palette filters them against the typed term.
 */
class SearchCommandResolver
{
    /** @var list<SearchCommand> */
    protected array $commands = [];

    /**
     * @param  array<int, class-string<SearchCommand>>  $commandClasses
     * @param  Authenticatable|null  $user  The panel user visibility checks run against.
     */
    public function __construct(array $commandClasses, protected ?Authenticatable $user = null)
    {
        foreach ($commandClasses as $class) {
            $this->commands[] = app($class);
        }
    }

    /** @return array<int, array{key: string, label: string, url: string, icon: string}> */
    public function resolve(): array
    {
        $visible = array_values(array_filter(
            $this->commands,
            fn (SearchCommand $command): bool => $command->visible($this->user),
        ));

        $ordered = (new OrderResolver)->sort(
            $visible,
            fn (SearchCommand $command): string => $command->key(),
            fn (SearchCommand $command): Position => $command->position(),
        );

        return array_map(fn (SearchCommand $command): array => $command->toArray(), $ordered);
    }
}
