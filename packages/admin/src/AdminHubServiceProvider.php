<?php

namespace GetCandy\Hub;

use GetCandy\Hub\Auth\Manifest;
use GetCandy\Hub\Console\Commands\InstallHub;
use GetCandy\Hub\Http\Livewire\Components\Account;
use GetCandy\Hub\Http\Livewire\Components\ActivityLogFeed;
use GetCandy\Hub\Http\Livewire\Components\Authentication\LoginForm;
use GetCandy\Hub\Http\Livewire\Components\Authentication\PasswordReset;
use GetCandy\Hub\Http\Livewire\Components\Avatar;
use GetCandy\Hub\Http\Livewire\Components\Collections\CollectionGroupShow;
use GetCandy\Hub\Http\Livewire\Components\Collections\CollectionGroupsIndex;
use GetCandy\Hub\Http\Livewire\Components\Collections\CollectionShow;
use GetCandy\Hub\Http\Livewire\Components\Collections\SideMenu;
use GetCandy\Hub\Http\Livewire\Components\CurrentStaffName;
use GetCandy\Hub\Http\Livewire\Components\Customers\CustomerShow;
use GetCandy\Hub\Http\Livewire\Components\Customers\CustomersIndex;
use GetCandy\Hub\Http\Livewire\Components\Orders\OrderShow;
use GetCandy\Hub\Http\Livewire\Components\Orders\OrdersIndex;
use GetCandy\Hub\Http\Livewire\Components\ProductOptions\OptionManager;
use GetCandy\Hub\Http\Livewire\Components\ProductOptions\OptionValueCreateModal;
use GetCandy\Hub\Http\Livewire\Components\Products\Editing\CustomerGroups;
use GetCandy\Hub\Http\Livewire\Components\Products\Options\OptionCreator;
use GetCandy\Hub\Http\Livewire\Components\Products\Options\OptionSelector;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductCreate;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductShow;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductsIndex;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeCreate;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypeShow;
use GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes\ProductTypesIndex;
use GetCandy\Hub\Http\Livewire\Components\Products\Variants\Editing\Inventory;
use GetCandy\Hub\Http\Livewire\Components\Products\Variants\VariantShow;
use GetCandy\Hub\Http\Livewire\Components\ProductSearch;
use GetCandy\Hub\Http\Livewire\Components\Reporting\ApexChart;
use GetCandy\Hub\Http\Livewire\Components\Settings\ActivityLog\ActivityLogIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Addons\AddonShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Addons\AddonsIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributeEdit;
use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributeGroupEdit;
use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributeShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Attributes\AttributesIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelCreate;
use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Channels\ChannelsIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Currencies\CurrenciesIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Currencies\CurrencyCreate;
use GetCandy\Hub\Http\Livewire\Components\Settings\Currencies\CurrencyShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Languages\LanguageCreate;
use GetCandy\Hub\Http\Livewire\Components\Settings\Languages\LanguageShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Languages\LanguagesIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Staff\StaffCreate;
use GetCandy\Hub\Http\Livewire\Components\Settings\Staff\StaffIndex;
use GetCandy\Hub\Http\Livewire\Components\Settings\Staff\StaffShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Tags\TagShow;
use GetCandy\Hub\Http\Livewire\Components\Settings\Tags\TagsIndex;
use GetCandy\Hub\Http\Livewire\Dashboard;
use GetCandy\Hub\Http\Livewire\HubLicense;
use GetCandy\Hub\Http\Livewire\Sidebar;
use GetCandy\Hub\Listeners\SetStaffAuthMiddlewareListener;
use GetCandy\Hub\Menu\MenuRegistry;
use GetCandy\Hub\Menu\SettingsMenu;
use GetCandy\Hub\Menu\SidebarMenu;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AdminHubServiceProvider extends ServiceProvider
{
    protected $configFiles = ['products', 'customers'];

    protected $root = __DIR__.'/..';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "getcandy-hub.$config");
        });
    }

    /**
     * Boot up the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'adminhub');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'adminhub');

        Auth::resolved(function ($auth) {
            $auth->extend('getcandyhub', function ($app, $name, array $config) {
                return $app->make(\GetCandy\Hub\Auth\HubGuard::class);
            });
        });

        $this->registerLivewireComponents();
        $this->registerAuthGuard();
        $this->registerPermissionManifest();
        $this->registerPublishables();

        // Commands
        if ($this->app->runningInConsole()) {
            collect($this->configFiles)->each(function ($config) {
                $this->publishes([
                    "{$this->root}/config/$config.php" => config_path("getcandy-hub/$config.php"),
                ], 'getcandy');
            });

            $this->commands([
                InstallHub::class,
            ]);
        }

        // Menu Builder
        $this->registerMenuBuilder();

        Event::listen(
            RouteMatched::class,
            [SetStaffAuthMiddlewareListener::class, 'handle']
        );

        // Handle generator
        Str::macro('handle', function ($string){
            return Str::slug($string, '_');
        });

        $this->app->singleton(\GetCandy\Hub\Editing\ProductSection::class, function ($app) {
            return new \GetCandy\Hub\Editing\ProductSection();
        });
    }

    protected function registerMenuBuilder()
    {
        $this->app->singleton(MenuRegistry::class, function () {
            return new MenuRegistry();
        });

        SidebarMenu::make();
        SettingsMenu::make();
    }

    /**
     * Register the hub's Livewire components.
     *
     * @return void
     */
    protected function registerLivewireComponents()
    {
        $this->registerGlobalComponents();
        $this->registerAuthenticationComponents();
        $this->registerProductComponents();
        $this->registerCollectionComponents();
        $this->registerReportingComponents();
        $this->registerSettingsComponents();
        $this->registerOrderComponents();
        $this->registerCustomerComponents();

        // Blade Components
        Blade::componentNamespace('GetCandy\\Hub\\Views\\Components', 'hub');
    }

    /**
     * Register global components.
     *
     * @return void
     */
    protected function registerGlobalComponents()
    {
        Livewire::component('sidebar', Sidebar::class);
        Livewire::component('dashboard', Dashboard::class);
        Livewire::component('hub-license', HubLicense::class);
        Livewire::component('hub.components.activity-log-feed', ActivityLogFeed::class);
        Livewire::component('hub.components.product-search', ProductSearch::class);
        Livewire::component('hub.components.account', Account::class);
        Livewire::component('hub.components.avatar', Avatar::class);
        Livewire::component('hub.components.current-staff-name', CurrentStaffName::class);
    }

    /**
     * Register the components used in the auth area.
     *
     * @return void
     */
    protected function registerAuthenticationComponents()
    {
        Livewire::component('hub.components.password-reset', PasswordReset::class);
        Livewire::component('hub.components.login-form', LoginForm::class);
    }

    protected function registerOrderComponents()
    {
        Livewire::component('hub.components.orders.index', OrdersIndex::class);
        Livewire::component('hub.components.orders.show', OrderShow::class);
    }

    protected function registerCustomerComponents()
    {
        Livewire::component('hub.components.customers.index', CustomersIndex::class);
        Livewire::component('hub.components.customers.show', CustomerShow::class);
    }

    /**
     * Register the components used in the products area.
     *
     * @return void
     */
    protected function registerProductComponents()
    {
        Livewire::component('hub.components.products.index', ProductsIndex::class);
        Livewire::component('hub.components.products.show', ProductShow::class);
        Livewire::component('hub.components.products.create', ProductCreate::class);

        Livewire::component('hub.components.products.product-types.index', ProductTypesIndex::class);
        Livewire::component('hub.components.products.product-types.show', ProductTypeShow::class);
        Livewire::component('hub.components.products.product-types.create', ProductTypeCreate::class);

        Livewire::component('hub.components.products.editing.customer-groups', CustomerGroups::class);

        Livewire::component('hub.components.products.options.option-creator', OptionCreator::class);
        Livewire::component('hub.components.products.options.option-selector', OptionSelector::class);

        Livewire::component('hub.components.products.variants.show', VariantShow::class);
        Livewire::component('hub.components.products.variants.editing.inventory', Inventory::class);

        Livewire::component('hub.components.product-options.option-manager', OptionManager::class);
        Livewire::component('hub.components.product-options.option-value-create-modal', OptionValueCreateModal::class);
    }

    /**
     * Register the components used in the collections area.
     *
     * @return void
     */
    protected function registerCollectionComponents()
    {
        Livewire::component('hub.components.collections.sidemenu', SideMenu::class);
        Livewire::component('hub.components.collections.collection-groups.index', CollectionGroupsIndex::class);
        Livewire::component('hub.components.collections.collection-groups.show', CollectionGroupShow::class);
        Livewire::component('hub.components.collections.show', CollectionShow::class);
    }

    /**
     * Register the components used in the reporting area.
     *
     * @return void
     */
    protected function registerReportingComponents()
    {
        Livewire::component('hub.components.reporting.apex-chart', ApexChart::class);
    }

    /**
     * Register the components used in the settings area.
     *
     * @return void
     */
    protected function registerSettingsComponents()
    {
        // Activity Log
        Livewire::component('hub.components.settings.activity-log.index', ActivityLogIndex::class);

        // Attributes
        Livewire::component('hub.components.settings.attributes.index', AttributesIndex::class);
        Livewire::component('hub.components.settings.attributes.show', AttributeShow::class);
        Livewire::component('hub.components.settings.attributes.attribute-group-edit', AttributeGroupEdit::class);
        Livewire::component('hub.components.settings.attributes.attribute-edit', AttributeEdit::class);

        // Channels
        Livewire::component('hub.components.settings.channels.index', ChannelsIndex::class);
        Livewire::component('hub.components.settings.channels.show', ChannelShow::class);
        Livewire::component('hub.components.settings.channels.create', ChannelCreate::class);

        // Users
        Livewire::component('hub.components.settings.staff.index', StaffIndex::class);
        Livewire::component('hub.components.settings.staff.show', StaffShow::class);
        Livewire::component('hub.components.settings.staff.create', StaffCreate::class);

        // Languages
        Livewire::component('hub.components.settings.languages.index', LanguagesIndex::class);
        Livewire::component('hub.components.settings.languages.create', LanguageCreate::class);
        Livewire::component('hub.components.settings.languages.show', LanguageShow::class);

        // Tags
        Livewire::component('hub.components.settings.tags.index', TagsIndex::class);
        Livewire::component('hub.components.settings.tags.show', TagShow::class);

        // Currencies
        Livewire::component('hub.components.settings.currencies.index', CurrenciesIndex::class);
        Livewire::component('hub.components.settings.currencies.show', CurrencyShow::class);
        Livewire::component('hub.components.settings.currencies.create', CurrencyCreate::class);

        // Addons
        Livewire::component('hub.components.settings.addons.index', AddonsIndex::class);
        Livewire::component('hub.components.settings.addons.show', AddonShow::class);
    }

    /**
     * Register our publishables.
     *
     * @return void
     */
    private function registerPublishables()
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/getcandy/admin-hub/'),
        ], 'getcandy:hub:public');
    }

    /**
     * Register our auth guard.
     *
     * @return void
     */
    protected function registerAuthGuard()
    {
        $this->app['config']->set('auth.guards.staff', [
            'driver' => 'getcandyhub',
        ]);
    }

    /**
     * Register our permissions manifest.
     *
     * @return void
     */
    protected function registerPermissionManifest()
    {
        $manifest = new Manifest();
        $this->app->instance(Manifest::class, $manifest);

        Gate::after(function ($user, $ability) use ($manifest) {
            // Are we trying to authorize something within the hub?
            $permission = $manifest->getPermissions()->first(fn ($permission) => $permission->handle === $ability);
            if ($permission) {
                return $user->admin || $user->authorize($ability);
            }
        });
    }
}
