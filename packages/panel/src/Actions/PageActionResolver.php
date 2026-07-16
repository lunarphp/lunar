<?php

namespace Lunar\Panel\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Lunar\Panel\Support\OrderResolver;
use Lunar\Panel\Support\Position;

/**
 * Resolves the registered page actions for a page id into ordered, visible,
 * context-aware descriptors ready to share to the frontend.
 */
class PageActionResolver
{
    /** @var PageAction[] */
    protected array $actions = [];

    /**
     * @param  array<int, class-string<PageAction>>  $actionClasses
     * @param  Authenticatable|null  $user  The panel user visibility checks run against.
     */
    public function __construct(array $actionClasses, protected ?Authenticatable $user = null)
    {
        foreach ($actionClasses as $class) {
            $this->actions[] = app($class);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resolve(mixed $context = null): array
    {
        $visible = array_values(array_filter(
            $this->actions,
            fn (PageAction $action) => $action->visible($context, $this->user),
        ));

        $ordered = (new OrderResolver)->sort(
            $visible,
            fn (PageAction $action): string => $action->key(),
            fn (PageAction $action): Position => $action->position(),
        );

        return array_map(fn (PageAction $action) => $action->toArray($context), $ordered);
    }
}
