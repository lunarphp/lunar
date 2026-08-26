<?php

namespace Lunar\Admin;

use Closure;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Filament\Widgets\Widget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Lunar\Admin\Filament\AvatarProviders\GravatarProvider;
use Lunar\Admin\Filament\Pages\Dashboard;
use Lunar\Admin\Filament\Resources\ActivityResource;
use Lunar\Admin\Filament\Resources\AttributeGroupResource;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Filament\Resources\ChannelResource;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView;
use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\CurrencyResource;
use Lunar\Admin\Filament\Resources\CustomerGroupResource;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\DiscountResource;
use Lunar\Admin\Filament\Resources\LanguageResource;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable;
use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Filament\Resources\StaffResource;
use Lunar\Admin\Filament\Resources\TagResource;
use Lunar\Admin\Filament\Resources\TaxClassResource;
use Lunar\Admin\Filament\Resources\TaxRateResource;
use Lunar\Admin\Filament\Resources\TaxZoneResource;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\AverageOrderValueChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\LatestOrdersTable;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\NewVsReturningCustomersChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrdersSalesChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderStatsOverview;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\OrderTotalsChart;
use Lunar\Admin\Filament\Widgets\Dashboard\Orders\PopularProductsTable;
use Lunar\Admin\Http\Controllers\DownloadPdfController;
use Lunar\Admin\Support\Facades\LunarAccessControl;

class LunarPanelManager
{
    protected bool $twoFactorAuthForced = false;

    protected bool $twoFactorAuthDisabled = false;

    protected ?Closure $closure = null;

    protected array $extensions = [];

    protected string $panelId = 'lunar';

    protected static $resources = [
        ActivityResource::class,
        AttributeGroupResource::class,
        BrandResource::class,
        ChannelResource::class,
        CollectionGroupResource::class,
        CollectionResource::class,
        CurrencyResource::class,
        CustomerGroupResource::class,
        CustomerResource::class,
        DiscountResource::class,
        LanguageResource::class,
        OrderResource::class,
        ProductOptionResource::class,
        ProductResource::class,
        ProductTypeResource::class,
        ProductVariantResource::class,
        StaffResource::class,
        TagResource::class,
        TaxClassResource::class,
        TaxZoneResource::class,
        TaxRateResource::class,
    ];

    protected static $pages = [
        Dashboard::class,
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

        if ($this->closure instanceof Closure) {
            $fn = $this->closure;
            $panel = $fn($panel);
        }

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
            'lunar::info' => 'lucide-info',
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

        if (app('request')->is($panel->getPath().'*')) {
            app('config')->set('livewire.inject_assets', true);
        }

        Table::configureUsing(function (Table $table): void {
            $table
                ->paginationPageOptions([10, 25, 50, 100, 250])
                ->defaultPaginationPageOption(25);
        });

        Section::configureUsing(fn (Section $section) => $section->columnSpanFull());
        Grid::configureUsing(fn (Grid $grid) => $grid->columnSpanFull());
        Fieldset::configureUsing(fn (Fieldset $fieldset) => $fieldset->columnSpanFull());

        // Livewire skips the TrimStrings middleware, so trim at the form layer instead.
        TextInput::configureUsing(fn (TextInput $input) => $input->trim());
        Textarea::configureUsing(fn (Textarea $textarea) => $textarea->trim());
        TagsInput::configureUsing(fn (TagsInput $tagsInput) => $tagsInput->trim());

        return $this;
    }

    public function panel(Closure $closure): self
    {
        $this->closure = $closure;

        return $this;
    }

    public function getPanel(): Panel
    {
        return Filament::getPanel($this->panelId);
    }

    public function forceTwoFactorAuth(bool $state = true): self
    {
        $this->twoFactorAuthForced = $state;

        return $this;
    }

    public function disableTwoFactorAuth(): self
    {
        $this->twoFactorAuthDisabled = true;

        return $this;
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

        $panelMiddleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];

        if (config('lunar.panel.pdf_rendering', 'download') == 'stream') {
            Route::get('lunar/pdf/download', DownloadPdfController::class)
                ->name('lunar.pdf.download')->middleware($panelMiddleware);
        }

        $plugins = [
            FilamentApexChartsPlugin::make(),
        ];

        $panel = Panel::make()
            ->spa()
            ->default()
            ->id($this->panelId)
            ->brandName('Lunar')
            ->brandLogo($brandAsset('lunar-logo.svg'))
            ->darkModeBrandLogo($brandAsset('lunar-logo-dark.svg'))
            ->favicon($brandAsset('lunar-icon.png'))
            ->brandLogoHeight('2rem')
            ->topbar(false)
            ->path('lunar')
            ->authGuard('staff')
            ->defaultAvatarProvider(GravatarProvider::class)
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->font('Poppins')
            ->middleware($panelMiddleware)
            ->assets([
                Css::make('lunar-panel', __DIR__.'/../resources/dist/lunar-panel.css'),
            ], 'lunarphp/panel')
            ->pages(
                static::getPages()
            )
            ->resources(
                static::getResources()
            )
            ->discoverClusters(
                in: realpath(__DIR__.'/Filament/Clusters'),
                for: 'Lunar\Admin\Filament\Clusters'
            )
            ->widgets(
                static::getWidgets()
            )
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins($plugins)
            ->discoverLivewireComponents(__DIR__.'/Livewire', 'Lunar\\Admin\\Livewire')
            ->livewireComponents([
                OrderItemsTable::class,
                CollectionTreeView::class,
            ])
            ->navigationGroups([
                'Catalog',
                'Sales',
                NavigationGroup::make()
                    ->label('Settings')
                    ->collapsed(),
            ])->sidebarCollapsibleOnDesktop()
            ->profile();

        if (! $this->twoFactorAuthDisabled) {
            $panel->multiFactorAuthentication(
                AppAuthentication::make()->recoverable(),
                isRequired: $this->twoFactorAuthForced,
            );
        }

        return $panel;
    }

    public function extensions(array $extensions): self
    {
        foreach ($extensions as $class => $extension) {
            if (! is_array($extension)) {
                $extension = [$extension];
            }

            $this->extensions[$class] = [
                ...$this->extensions[$class] ?? [],
                ...collect($extension)->reject(
                    fn ($extension) => ! class_exists($extension)
                )->map(
                    fn ($extension) => app($extension)
                )->values()->toArray(),
            ];
        }

        return $this;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array<class-string<resource>>
     */
    public static function getResources(): array
    {
        return static::$resources;
    }

    /**
     * @return array<class-string<Page>>
     */
    public static function getPages(): array
    {
        return static::$pages;
    }

    /**
     * @return array<class-string<Widget>>
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

    public function callHook(string $class, ?object $caller, string $hookName, ...$args): mixed
    {
        if (isset($this->extensions[$class])) {
            foreach ($this->extensions[$class] as $extension) {
                if (method_exists($extension, $hookName)) {
                    $extension->setCaller($caller);
                    $args[0] = $extension->{$hookName}(...$args);
                }
            }
        }

        return $args[0];
    }
}
