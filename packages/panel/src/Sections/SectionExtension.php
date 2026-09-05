<?php

namespace Lunar\Panel\Sections;

use Closure;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Dashboard\Widget;
use Lunar\Panel\Navigation\NavigationRegistry;
use Lunar\Panel\Search\SearchCommand;
use Lunar\Panel\Search\SearchSource;
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
     * Return extension classes keyed by table id; each value is one class
     * or a list of them.
     *
     * @return array<string, class-string|array<int, class-string>>
     */
    public function tableExtensions(): array
    {
        return [];
    }

    /**
     * @return array<string, array<int, class-string>>
     */
    public function pageActions(): array
    {
        return [];
    }

    /**
     * Return draftable-resource definitions for edit forms this extension
     * contributes, e.g. [CustomerDraftResource::class].
     *
     * @return array<int, class-string<DraftableResource>>
     */
    public function draftables(): array
    {
        return [];
    }

    /**
     * Return panel forms for discount types this extension contributes, keyed
     * by the discount type class, e.g.
     * [PercentageOff::class => PercentageOffForm::class].
     *
     * @return array<class-string, class-string<DiscountTypeForm>>
     */
    public function discountTypeForms(): array
    {
        return [];
    }

    /**
     * Return dashboard widget classes this extension contributes, e.g.
     * [RevenueChartWidget::class].
     *
     * @return array<int, class-string<Widget>>
     */
    public function widgets(): array
    {
        return [];
    }

    /**
     * Return global-search sources this extension contributes.
     *
     * @return array<int, class-string<SearchSource>>
     */
    public function searchSources(): array
    {
        return [];
    }

    /**
     * Return global-search commands this extension contributes.
     *
     * @return array<int, class-string<SearchCommand>>
     */
    public function searchCommands(): array
    {
        return [];
    }

    /**
     * Return a Vite config to register for this extension, or null to skip.
     * Registered under a key derived from the target section and this class
     * ("{section}-{kebab-class-name}"), so it never collides with the
     * section's own config or a sibling extension's.
     *
     * @return array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|null
     */
    public function vite(): array|string|null
    {
        return null;
    }

    /**
     * Translator namespaces whose lang groups the panel serves to the
     * frontend (as `{namespace}::{group}` message keys).
     *
     * @return array<int, string>
     */
    public function langNamespaces(): array
    {
        return [];
    }
}
