<?php

namespace Lunar\Panel\Sections;

use Closure;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Slots\SlotRegistry;

abstract class SectionExtension implements ProvidesNavigation
{
    abstract public function extends(): string;

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
     * Return a Vite config to register for this section, or null to skip.
     *
     * @return array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|null
     */
    public function vite(): array|string|null
    {
        return null;
    }
}
