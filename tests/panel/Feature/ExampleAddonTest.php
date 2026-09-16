<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Panel\PanelManager;
use Lunar\Tests\Panel\Fixtures\ExampleAddonTestCase;

uses(ExampleAddonTestCase::class);

it('renders the example add-on own page for an authenticated admin', function () {
    $this->get('/panel/example-addon')->assertRedirect(route('panel.login'));

    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel/example-addon')
        ->assertOk()
        // shouldExist: false — the component is resolved client-side via
        // window.LunarPanel.registerPages(), not a namespaced Blade view, so
        // Inertia's testing view-finder has no on-disk path to check.
        ->assertInertia(fn (Assert $page) => $page
            ->component('example-addon::Widgets/Index', false)
            // The demo table rows, each carrying its per-row action URL map.
            ->has('widgets', 3)
            ->where('widgets.0._actions.ping', route('panel.example-addon.widgets.ping', 1)));
});

it('contributes a dashboard widget with deferred data', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    Customer::factory()->count(2)->create();

    $this->actingAs($staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('widgets', 10)
            ->where('widgets.9.key', 'example-addon-customers')
            ->where('widgets.9.component', 'example-addon::CustomerCountWidget')
            // Hidden by default: it appears in the Customise dialog, and no
            // data is deferred for it until the staff member adds it.
            ->where('widgets.9.visible', false)
            ->loadDeferredProps(fn (Assert $props) => $props
                ->missing('widgetData.example-addon-customers')
            )
        );
});

it('flashes back from the widget table row action', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->from('/panel/example-addon')
        ->get(route('panel.example-addon.widgets.ping', 2))
        ->assertRedirect('/panel/example-addon')
        ->assertSessionHas('success');
});

it('denies the example add-on routes to staff without the customers permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff');

    $this->get('/panel/example-addon')->assertForbidden();
    $this->get('/panel/example-addon/import')->assertForbidden();

    $this->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', fn ($groups) => ! collect($groups)
                ->pluck('key')
                ->contains('example-addon-group'))
        );
});

it('shows the example add-on navigation to staff', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', function ($groups) {
                $group = collect($groups)->firstWhere('key', 'example-addon-group');

                return $group !== null
                    && collect($group['items'])->firstWhere('key', 'example-addon')['icon'] === 'tag';
            })
        );
});

it('merges the example add-on table extension column onto the real customer index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    Address::factory()->count(2)->for($customer)->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', function ($columns) {
                $keys = collect($columns)->pluck('key')->all();

                // First-party columns come first; the example add-on's
                // ExampleColumn ("id") is appended by the table extension resolver.
                return $keys === ['full_name', 'company_name', 'customer_groups', 'orders_count', 'total_spend', 'last_order_at', 'id'];
            })
            ->where('customers.data.0.id', $customer->id)
        );
});

it('shares the example add-on slot entry on the real customer edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The zone key contains dots ("customers.edit:main:after"), so it
            // can't be reached via dot-notation where()/has() path assertions.
            ->where('slots', function ($slots) {
                $zone = $slots->get('customers.edit:main:after');

                return $zone !== null
                    && collect($zone)->contains(
                        fn ($entry) => $entry['component'] === 'example-addon::InfoBanner'
                    );
            })
        );
});

it('shares the SEO card slot entry on the product edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $product = Product::factory()->create();

    $this->get(route('panel.products.edit', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', function ($slots) {
                $zone = $slots->get('products.edit:content:after');

                return $zone !== null
                    && collect($zone)->contains(
                        fn ($entry) => $entry['component'] === 'example-addon::SeoCard'
                    );
            })
        );
});

it('resolves the example add-on table extension directly', function () {
    $resolver = app(PanelManager::class)->resolveExtensions('customers.index');

    expect(array_column($resolver->getColumns(), 'key'))->toBe(['id']);
});

it('injects an example row action anchored after the first-party edit action', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // after('edit') places the example action between edit and delete.
            ->where('tableActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['edit', 'example-ping', 'delete'])
            ->where('customers.data.0._actions.example-ping', route('panel.example-addon.ping', $customer))
        );
});

it('injects an example bulk action into the customers table', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableBulkActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['example-bulk-ping'])
        );
});

it('injects an example listing-page action on the customers index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pageActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['example-import'])
            ->where('pageActions.0.url', route('panel.example-addon.import'))
        );
});

it('injects an example record-page action with a per-record url on the customer edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pageActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['example-audit'])
            ->where('pageActions.0.url', route('panel.example-addon.audit', $customer))
        );
});

it('applies the example add-on filter to the real customer index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $referenced = Customer::factory()->create(['account_ref' => 'ACME-001']);
    Customer::factory()->create(['account_ref' => null]);

    $this->get(route('panel.customers.index', ['filter' => ['has_account_ref' => 'yes']]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableFilters.0.key', 'has_account_ref')
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $referenced->id)
        );
});

it('registers the example add-on vite module', function () {
    $vites = app(PanelManager::class)->registeredVites();

    expect($vites)->toHaveKey('example-addon')
        ->and($vites['example-addon']['buildDirectory'])->toBe('vendor/lunar-panel/example-addon');
});

it('symlinks the example add-on build directory via lunar:panel:link', function () {
    $buildPath = dirname(__DIR__, 3).'/packages/panel-addon-example/build';

    // The example's compiled build/ is gitignored, so it is absent on CI —
    // the command only links directories that exist.
    $createdBuildDir = ! is_dir($buildPath);

    if ($createdBuildDir) {
        mkdir($buildPath, 0755, true);
    }

    $target = public_path('vendor/lunar-panel/example-addon');

    if (is_link($target)) {
        unlink($target);
    }

    try {
        $this->artisan('lunar:panel:link')->assertSuccessful();

        expect(is_link($target))->toBeTrue()
            ->and(readlink($target))->toBe($buildPath);
    } finally {
        // The Testbench skeleton's public/ is shared across runs; leave it clean.
        if (is_link($target)) {
            unlink($target);
        }

        if ($createdBuildDir) {
            rmdir($buildPath);
        }
    }
});

it('resolves the example add-on nav labels through its lang namespace', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get('/panel')
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.groups', function ($groups) {
                $group = collect($groups)->firstWhere('key', 'example-addon-group');

                return $group !== null && $group['label'] === 'Example Add-on';
            })
        );
});

it('serves the example add-on lang groups from the translations endpoint', function () {
    $this->getJson('/panel/translations/en')
        ->assertOk()
        ->assertJsonPath('messages.example-addon::example.title', 'Example Add-on');

    // The example ships fr, so the namespaced group follows the locale.
    $title = $this->getJson('/panel/translations/fr')
        ->assertOk()
        ->json('messages.example-addon::example.title');

    expect($title)->not->toBe('Example Add-on');
});

it('renders the example add-on settings screen for an authenticated admin', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.settings.example-addon.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('example-addon::Settings/Index', false)
            ->where('settings.webhook_url', '')
            ->where('settings.ping_enabled', true)
            ->where('urls.update', route('panel.settings.example-addon.update')));
});

it('adds the example add-on group to the settings navigation', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->get(route('panel.settings.example-addon.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settingsNavigation.groups', function ($groups) {
                $group = collect($groups)->firstWhere('key', 'example-addon');

                return $group !== null
                    && $group['label'] === 'Example Add-on'
                    && collect($group['items'])->firstWhere('key', 'example-addon-settings')['url']
                        === route('panel.settings.example-addon.index');
            })
        );
});

it('round-trips the example add-on settings through the update endpoint', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->from(route('panel.settings.example-addon.index'))
        ->post(route('panel.settings.example-addon.update'), [
            'webhook_url' => 'https://example.com/hooks',
            'ping_enabled' => false,
        ])
        ->assertRedirect(route('panel.settings.example-addon.index'))
        ->assertSessionHas('success');

    $this->get(route('panel.settings.example-addon.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.webhook_url', 'https://example.com/hooks')
            ->where('settings.ping_enabled', false));
});

it('validates the example add-on settings update', function () {
    $staff = Staff::factory()->create(['admin' => true]);

    $this->actingAs($staff, 'staff')
        ->from(route('panel.settings.example-addon.index'))
        ->post(route('panel.settings.example-addon.update'), [
            'webhook_url' => 'not-a-url',
            'ping_enabled' => true,
        ])
        ->assertRedirect(route('panel.settings.example-addon.index'))
        ->assertSessionHasErrors('webhook_url');
});

it('denies the example add-on settings screen to staff without the permission', function () {
    $staff = Staff::factory()->create(['admin' => false]);

    $this->actingAs($staff, 'staff');

    $this->get(route('panel.settings.example-addon.index'))->assertForbidden();
    $this->post(route('panel.settings.example-addon.update'))->assertForbidden();
});

it('contributes a global search source and quick action', function () {
    Language::factory()->create(['default' => true]);

    $staff = Staff::factory()->create(['admin' => true]);
    $customer = Customer::factory()->create(['account_ref' => 'ACC-5521', 'first_name' => 'Ada', 'last_name' => 'Lovelace']);

    $rows = collect(
        $this->actingAs($staff, 'staff')->getJson('/panel/search?q=ACC-5521')->assertOk()->json('data')
    );

    $row = $rows->firstWhere('kind', 'example-accounts');

    expect($row['id'])->toBe($customer->id)
        ->and($row['label'])->toBe('ACC-5521')
        ->and($row['hint'])->toBe('Ada Lovelace')
        ->and($row['kind_label'])->toBe('Account references');

    $this->actingAs($staff, 'staff')
        ->get(route('panel.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('searchCommands.6.key', 'example-addon.widgets')
            ->where('searchCommands.6.label', 'Ping widgets')
            ->where('searchSources.5.key', 'example-accounts'));
});

it('hides the add-on search source from staff without its permission', function () {
    Language::factory()->create(['default' => true]);

    $staff = Staff::factory()->create(['admin' => false]);
    $staff->givePermissionTo('catalog:manage-products');

    Customer::factory()->create(['account_ref' => 'ACC-9001']);

    $rows = collect(
        $this->actingAs($staff, 'staff')->getJson('/panel/search?q=ACC-9001')->assertOk()->json('data')
    );

    expect($rows)->toBeEmpty();
});

it('shares the loyalty card slot and its slice values on the customer edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create(['meta' => ['loyalty_tier' => 'silver']]);

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', fn ($slots) => collect($slots->get('customers.edit:main:after'))
                ->contains(fn ($entry) => $entry['component'] === 'example-addon::LoyaltyCard'))
            ->where('formSliceValues.addon:example-addon:tier', 'silver'));
});

it('drafts, conflicts and commits the loyalty tier with the customer through the real draft routes', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($staff, 'staff');

    $customer = Customer::factory()->create(['first_name' => 'Ada']);

    // Autosave stores the slice field under its addon-prefixed key, normalised
    // by the slice ('' for "no tier" becomes null).
    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['first_name' => 'Grace', 'addon:example-addon:tier' => 'gold'],
    ])->assertOk()->assertJsonPath('data.addon:example-addon:tier', 'gold');

    expect(EditDraft::sole()->base_snapshot)->toBe(['first_name' => 'Ada', 'addon:example-addon:tier' => null]);

    // A concurrent change to the tier surfaces as a conflict labelled by the
    // add-on's own lang key, and nothing commits.
    $customer->update(['meta' => ['loyalty_tier' => 'silver']]);

    $this->postJson(route('panel.customers.draft.commit', $customer), ['data' => [], 'rebase' => []])
        ->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'addon:example-addon:tier')
        ->assertJsonPath('conflicts.0.label', 'Loyalty tier')
        ->assertJsonPath('conflicts.0.theirs', 'silver');

    expect($customer->refresh()->first_name)->toBe('Ada');

    // Resolved against the current value, the commit lands both the
    // customer's own field and the add-on's in one go.
    $this->postJson(route('panel.customers.draft.commit', $customer), [
        'data' => ['addon:example-addon:tier' => 'gold'],
        'rebase' => ['addon:example-addon:tier' => 'silver'],
    ])->assertOk();

    $customer->refresh();

    expect($customer->first_name)->toBe('Grace')
        ->and($customer->meta['loyalty_tier'])->toBe('gold')
        ->and(EditDraft::count())->toBe(0);
});

it('validates the loyalty tier under its prefixed key', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['addon:example-addon:tier' => 'platinum'],
    ])->assertOk();

    $this->postJson(route('panel.customers.draft.commit', $customer), ['data' => [], 'rebase' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor('addon:example-addon:tier');
});

it('refuses an add-on slice key outside its own namespace', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    // The add-on registered under `addon:example-addon`; the bare namespace
    // is not a field the customer draft knows.
    $this->patchJson(route('panel.customers.draft.update', $customer), [
        'data' => ['example-addon:tier' => 'gold'],
    ])->assertUnprocessable();
});

it('saves the loyalty tier with a customer created through the plain create form', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('slots', fn ($slots) => collect($slots->get('customers.create:main:after'))
                ->contains(fn ($entry) => $entry['component'] === 'example-addon::LoyaltyCard'))
            ->where('formSliceValues.addon:example-addon:tier', null));

    $this->post(route('panel.customers.store'), [
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'addon:example-addon:tier' => 'silver',
    ])->assertRedirect();

    expect(Customer::sole()->meta['loyalty_tier'])->toBe('silver');

    // The slice's rules apply once its namespace is posted.
    $this->post(route('panel.customers.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'addon:example-addon:tier' => 'platinum',
    ])->assertSessionHasErrors('addon:example-addon:tier');
});
