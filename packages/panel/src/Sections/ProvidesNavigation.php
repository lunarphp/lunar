<?php

namespace Lunar\Panel\Sections;

use Closure;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Slots\SlotRegistry;

interface ProvidesNavigation
{
    public function navigation(NavigationRegistry $registry): void;

    public function settingsNavigation(NavigationRegistry $registry): void;

    public function slots(SlotRegistry $registry): void;

    public function routes(): ?Closure;

    /**
     * @return array<string, string>
     */
    public function tableExtensions(): array;

    /**
     * Page actions keyed by page id, e.g. ['customers.edit' => [ImpersonateAction::class]].
     *
     * @return array<string, array<int, class-string>>
     */
    public function pageActions(): array;

    /**
     * @return array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|null
     */
    public function vite(): array|string|null;
}
