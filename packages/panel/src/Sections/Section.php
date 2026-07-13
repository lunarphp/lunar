<?php

namespace Lunar\Panel\Sections;

use Closure;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Slots\SlotRegistry;

abstract class Section implements ProvidesNavigation
{
    abstract public function key(): string;

    public function label(): string
    {
        return ucfirst($this->key());
    }

    public function navigation(NavigationRegistry $registry): void {}

    public function settingsNavigation(NavigationRegistry $registry): void {}

    public function slots(SlotRegistry $registry): void {}

    public function routes(): ?Closure
    {
        return null;
    }

    /**
     * Return an array of [tableId => extensionClass] pairs.
     *
     * @return array<string, string>
     */
    public function tableExtensions(): array
    {
        return [];
    }

    /**
     * Return page actions keyed by page id, e.g.
     * ['customers.edit' => [ImpersonateAction::class]].
     *
     * @return array<string, array<int, class-string>>
     */
    public function pageActions(): array
    {
        return [];
    }

    /**
     * Return a Vite config to register for this section, or null to skip.
     *
     * @return array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|null
     */
    public function vite(): array|string|null
    {
        return null;
    }
}
