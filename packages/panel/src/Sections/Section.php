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
     * Return draftable-resource definitions for edit forms in this section,
     * e.g. [CustomerDraftResource::class].
     *
     * @return array<int, class-string<DraftableResource>>
     */
    public function draftables(): array
    {
        return [];
    }

    /**
     * Return panel forms for discount types this section owns, keyed by the
     * discount type class, e.g.
     * [PercentageOff::class => PercentageOffForm::class].
     *
     * A type with no entry falls back to the raw JSON editor, so a
     * panel-unaware type from another package stays editable.
     *
     * @return array<class-string, class-string<DiscountTypeForm>>
     */
    public function discountTypeForms(): array
    {
        return [];
    }

    /**
     * Return global-search sources this section contributes, e.g.
     * [ProductSearchSource::class].
     *
     * @return array<int, class-string<SearchSource>>
     */
    public function searchSources(): array
    {
        return [];
    }

    /**
     * Return global-search commands this section contributes — the static
     * verbs the palette offers alongside record results, e.g.
     * [CreateProductCommand::class].
     *
     * @return array<int, class-string<SearchCommand>>
     */
    public function searchCommands(): array
    {
        return [];
    }

    /**
     * Return dashboard widget classes this section contributes, e.g.
     * [RevenueChartWidget::class].
     *
     * @return array<int, class-string<Widget>>
     */
    public function widgets(): array
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
