<?php

namespace Lunar\Panel\Sections;

use Closure;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Panel\Contracts\DraftableResource;
use Lunar\Panel\Dashboard\Widget;
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
     * Draftable-resource definitions for edit forms, e.g.
     * [CustomerDraftResource::class].
     *
     * @return array<int, class-string<DraftableResource>>
     */
    public function draftables(): array;

    /**
     * Panel forms for discount types, keyed by the discount type class, e.g.
     * [PercentageOff::class => PercentageOffForm::class].
     *
     * @return array<class-string, class-string<DiscountTypeForm>>
     */
    public function discountTypeForms(): array;

    /**
     * Dashboard widget classes, e.g. [RevenueChartWidget::class].
     *
     * @return array<int, class-string<Widget>>
     */
    public function widgets(): array;

    /**
     * @return array{input?: string|string[], hotFile?: string|null, buildDirectory?: string, __buildSourcePath?: string}|string|null
     */
    public function vite(): array|string|null;

    /**
     * Translator namespaces whose lang groups the panel serves to the frontend
     * (as `{namespace}::{group}` message keys), e.g. ['my-addon'].
     *
     * @return array<int, string>
     */
    public function langNamespaces(): array;
}
