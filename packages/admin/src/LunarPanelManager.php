<?php

namespace Lunar\Admin;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Lunar\Admin\Filament\AvatarProviders\GravatarProvider;
use Lunar\Admin\Filament\Pages;
use Lunar\Admin\Filament\Resources;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable;
use Lunar\Admin\Support\Extending\BaseExtension;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Admin\Support\Facades\LunarAccessControl;

class LunarPanelManager
{
    protected ?\Closure $closure = null;

    protected array $extensions = [];

    protected string $panelId = 'lunar';

    protected static $resources = [
        Resources\ActivityResource::class,
        Resources\AttributeGroupResource::class,
        Resources\BrandResource::class,
        Resources\ChannelResource::class,
        Resources\CollectionGroupResource::class,
        Resources\CollectionResource::class,
        Resources\CurrencyResource::class,
        Resources\CustomerGroupResource::class,
        Resources\CustomerResource::class,
        Resources\DiscountResource::class,
        Resources\LanguageResource::class,
        Resources\OrderResource::class,
        Resources\ProductOptionResource::class,
        Resources\ProductResource::class,
        Resources\ProductTypeResource::class,
        Resources\ProductVariantResource::class,
        Resources\StaffResource::class,
        Resources\TagResource::class,
        Resources\TaxClassResource::class,
        Resources\TaxZoneResource::class,
    ];

    protected static $pages = [
        Pages\Dashboard::class,
    ];

    protected static $widgets = [
        OrderStatsOverview::class,
        OrderTotalsChart::class,
        OrdersSalesChart::class,
        AverageOrderValueChart::class,
        NewVsReturningCustomersChart::class,
        PopularProductsTable::class,
        LatestOrdersTable::class,
    ];

    public function register(): self
    {
        $panel = $this->defaultPanel();

        if ($this->closure instanceof \Closure) {
            $fn = $this->closure;
            $panel = $fn($panel);
        }

        $panel->id($this->panelId);

        Filament::registerPanel($panel);

        FilamentIcon::register([
            // Filament
            'panels::topbar.global-search.field' => 'lucide-search',
            'actions::view-action' => 'lucide-eye',
            'actions::edit-action' => 'lucide-edit',
            'actions::delete-action' => 'lucide-trash-2',
            'actions::make-collection-root-action' => 'lucide-corner-left-up',

            // Lunar
            'lunar::activity' => 'lucide-activity',
            'lunar::attributes' => 'lucide-pencil-ruler',
            'lunar::availability' => 'lucide-calendar',
            'lunar::basic-information' => 'lucide-edit',
            'lunar::brands' => 'lucide-badge-check',
            'lunar::channels' => 'lucide-store',
            'lunar::collections' => 'lucide-blocks',
            'lunar::sub-collection' => 'lucide-square-stack',
            'lunar::move-collection' => 'lucide-move',
            'lunar::currencies' => 'lucide-circle-dollar-sign',
            'lunar::customers' => 'lucide-users',
            'lunar::customer-groups' => 'lucide-users',
            'lunar::dashboard' => 'lucide-bar-chart-big',
            'lunar::discounts' => 'lucide-percent-circle',
            'lunar::discount-limitations' => 'lucide-list-x',
            'lunar::languages' => 'lucide-languages',
            'lunar::media' => 'lucide-image',
            'lunar::orders' => 'lucide-inbox',
            'lunar::product-pricing' => 'lucide-coins',
            'lunar::product-associations' => 'lucide-cable',
            'lunar::product-inventory' => 'lucide-combine',
            'lunar::product-options' => 'lucide-list',
            'lunar::product-shipping' => 'lucide-truck',
            'lunar::product-variants' => 'lucide-shapes',
            'lunar::products' => 'lucide-tag',
            'lunar::staff' => 'lucide-shield',
            'lunar::tags' => 'lucide-tags',
            'lunar::tax' => 'lucide-landmark',
            'lunar::urls' => 'lucide-globe',
            'lunar::product-identifiers' => 'lucide-package-search',
            'lunar::reorder' => 'lucide-grip-vertical',
            'lunar::chevron-right' => 'lucide-chevron-right',
            'lunar::image-placeholder' => 'lucide-image',
            'lunar::trending-up' => 'lucide-trending-up',
            'lunar::trending-down' => 'lucide-trending-down',
            'lunar::exclamation-circle' => 'lucide-alert-circle',
        ]);

        FilamentColor::register([
            'chartPrimary' => Color::Blue,
            'chartSecondary' => Color::Green,
        ]);

        return $this;
    }

    public function panel(\Closure $closure): self
    {
        $this->closure = $closure;

        return $this;
    }

    public function getPanel(): Panel
    {
        return Filament::getPanel($this->panelId);
    }

    protected function defaultPanel(): Panel
    {
        $brandAsset = function ($asset) {
            $vendorPath = 'vendor/lunarpanel/';

            if (file_exists(public_path($vendorPath.$asset))) {
                return asset($vendorPath.$asset);
            } else {
                $type = str($asset)
                    ->endsWith('.png') ? 'image/png' : 'image/svg+xml';

                return "data:{$type};base64,".base64_encode(file_get_contents(__DIR__.'/../public/'.$asset));
            }
        };

        return Panel::make()
            ->spa()
            ->default()
            ->id($this->panelId)
            ->brandName('Lunar')
            ->brandLogo($brandAsset('lunar-logo.svg'))
            ->darkModeBrandLogo($brandAsset('lunar-logo-dark.svg'))
            ->favicon($brandAsset('lunar-icon.png'))
            ->brandLogoHeight('2rem')
            ->path('lunar')
            ->authGuard('staff')
            ->defaultAvatarProvider(GravatarProvider::class)
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->font('Poppins')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->pages(
                static::getPages()
            )
            ->resources(
                static::getResources()
            )
            ->widgets(
                static::getWidgets()
            )
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::make(),
            ])
            ->discoverLivewireComponents(__DIR__.'/Livewire', 'Lunar\\Admin\\Livewire')
            ->livewireComponents([
                Resources\OrderResource\Pages\Components\OrderItemsTable::class,
                \Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView::class,
            ])
            ->navigationGroups([
                'Catalog',
                'Sales',
                NavigationGroup::make()
                    ->label('Settings')
                    ->collapsed(),
            ])->sidebarCollapsibleOnDesktop();
    }

    public function registerExtension(BaseExtension|ResourceExtension $extension, string $pageClass): self
    {
        $this->extensions[$pageClass][] = $extension;

        return $this;
    }

    public static function getResources()
    {
        return static::$resources;
    }

    public static function getPages()
    {
        return static::$pages;
    }

    /**
     * @return string[]
     */
    public static function getWidgets(): array
    {
        return static::$widgets;
    }

    public function useRoleAsAdmin(string|array $roleHandle): self
    {
        LunarAccessControl::useRoleAsAdmin($roleHandle);

        return $this;
    }

    public function callHook(string $class, string $hookName, ...$args): mixed
    {
        if (isset($this->extensions[$class])) {
            foreach ($this->extensions[$class] as $extension) {
                if (method_exists($extension, $hookName)) {
                    $args[0] = $extension->{$hookName}(...$args);
                }
            }
        }

        return $args[0];
    }
}
