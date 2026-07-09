<?php

namespace Lunar\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Lunar\Filament\Widgets\Collections\CollectionTreeView;
use Lunar\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Filament\Widgets\Dashboard\Orders\PopularProductsTable;

class LunarPlugin implements Plugin
{
    protected bool $widgets = false;

    protected bool $globalSearch = false;

    protected bool $actions = false;

    protected bool $livewireComponents = false;

    /**
     * @var array<class-string>
     */
    protected array $extraWidgets = [];

    /**
     * @var array<class-string>
     */
    protected array $extraLivewireComponents = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'lunar';
    }

    /**
     * Enable everything the bridge ships.
     */
    public function fullPreset(): static
    {
        return $this
            ->widgets()
            ->globalSearch()
            ->actions()
            ->livewireComponents();
    }

    /**
     * Register Lunar's first-party dashboard widgets.
     *
     * @param  array<class-string>|bool  $extras  When an array, additional widgets to register alongside the defaults. When false, disables the feature entirely.
     */
    public function widgets(array|bool $extras = true): static
    {
        if ($extras === false) {
            $this->widgets = false;

            return $this;
        }

        $this->widgets = true;

        if (is_array($extras)) {
            $this->extraWidgets = array_merge($this->extraWidgets, $extras);
        }

        return $this;
    }

    /**
     * Enable Lunar's global-search descriptors. Resources opt-in by extending GlobalSearchDescriptor.
     */
    public function globalSearch(bool $enabled = true): static
    {
        $this->globalSearch = $enabled;

        return $this;
    }

    /**
     * Enable Lunar's first-party actions library. Actions are referenced directly by resources/pages; this flag is a toggle for downstream wiring.
     */
    public function actions(bool $enabled = true): static
    {
        $this->actions = $enabled;

        return $this;
    }

    /**
     * Register Lunar's bridge Livewire components on the panel.
     *
     * @param  array<class-string>|bool  $extras  Additional Livewire components, or false to disable.
     */
    public function livewireComponents(array|bool $extras = true): static
    {
        if ($extras === false) {
            $this->livewireComponents = false;

            return $this;
        }

        $this->livewireComponents = true;

        if (is_array($extras)) {
            $this->extraLivewireComponents = array_merge($this->extraLivewireComponents, $extras);
        }

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->widgets) {
            $panel->widgets(array_merge($this->defaultWidgets(), $this->extraWidgets));
        }

        if ($this->livewireComponents) {
            $panel->livewireComponents(array_merge($this->defaultLivewireComponents(), $this->extraLivewireComponents));
        }
    }

    public function boot(Panel $panel): void
    {
        // The bridge's class-based actions and global-search descriptors are referenced directly
        // from resources/pages. Toggles are persisted via config for downstream consumers that
        // want to gate UI affordances on whether the plugin enabled them.
        config([
            'lunar.filament.plugin.global_search' => $this->globalSearch,
            'lunar.filament.plugin.actions' => $this->actions,
        ]);
    }

    /**
     * @return array<class-string>
     */
    protected function defaultWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            OrderTotalsChart::class,
            OrdersSalesChart::class,
            AverageOrderValueChart::class,
            NewVsReturningCustomersChart::class,
            PopularProductsTable::class,
            LatestOrdersTable::class,
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function defaultLivewireComponents(): array
    {
        return [
            CollectionTreeView::class,
        ];
    }
}
