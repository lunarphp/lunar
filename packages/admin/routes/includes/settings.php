<?php

use GetCandy\Hub\Http\Livewire\Pages\Settings\ActivityLog\ActivityLogIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Addons\AddonShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Addons\AddonsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes\AttributeShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes\AttributesIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Channels\ChannelCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Channels\ChannelShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Channels\ChannelsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Currencies\CurrenciesIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Currencies\CurrencyCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Currencies\CurrencyShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Languages\LanguageCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Languages\LanguageShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Languages\LanguagesIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\CustomerGroups\CustomerGroupCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\CustomerGroups\CustomerGroupShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\CustomerGroups\CustomerGroupsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Staff\StaffCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Staff\StaffIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Staff\StaffShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Tags\TagShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Tags\TagsIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes\TaxClassesIndex;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes\TaxZoneCreate;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes\TaxZoneShow;
use GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes\TaxZonesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('hub.channels.index');
})->name('hub.settings');

/**
 * Activity Log.
 */
Route::get('activity-log', ActivityLogIndex::class)->name('hub.activity-log.index');

/**
 * Attribute routes.
 */
Route::group([
    'middleware' => 'can:settings:manage-attributes',
], function () {
    Route::get('attributes', AttributesIndex::class)->name('hub.attributes.index');
    Route::get('attributes/{type}', AttributeShow::class)->name('hub.attributes.show');
});

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:settings:core',
], function () {
    Route::get('channels', ChannelsIndex::class)->name('hub.channels.index');
    Route::get('channels/create', ChannelCreate::class)->name('hub.channels.create');
    Route::get('channels/{channel}', ChannelShow::class)->name('hub.channels.show');
});

/**
 * Staff.
 */
Route::group([
    'middleware' => 'can:settings:manage-staff',
], function () {
    Route::get('staff', StaffIndex::class)->name('hub.staff.index');
    Route::get('staff/create', StaffCreate::class)->name('hub.staff.create');
    Route::get('staff/{staff}', StaffShow::class)->withTrashed()->name('hub.staff.show');
});

/**
 * Customer Groups.
 */
Route::group([
    'middleware' => 'can:settings:manage-staff',
], function () {
    Route::get('customer-groups', CustomerGroupsIndex::class)->name('hub.customer-groups.index');
    Route::get('customer-groups/create', CustomerGroupCreate::class)->name('hub.customer-groups.create');
    Route::get('customer-groups/{customerGroup}', CustomerGroupShow::class)->withTrashed()->name('hub.customer-groups.show');
});

/*
/**
 * Languages.
 */
Route::group([
    'middleware' => 'can:settings:core',
], function () {
    Route::get('languages', LanguagesIndex::class)->name('hub.languages.index');
    Route::get('languages/create', LanguageCreate::class)->name('hub.languages.create');
    Route::get('languages/{language}', LanguageShow::class)->withTrashed()->name('hub.languages.show');
});

/**
 * Currencies.
 */
Route::group([
    'middleware' => 'can:settings:core',
], function () {
    Route::get('currencies', CurrenciesIndex::class)->name('hub.currencies.index');
    Route::get('currencies/create', CurrencyCreate::class)->name('hub.currencies.create');
    Route::get('currencies/{currency}', CurrencyShow::class)->name('hub.currencies.show');
});

/*
 * Addons.
 */
Route::group([
    'middleware' => 'can:settings',
], function () {
    Route::get('addons', AddonsIndex::class)->name('hub.addons.index');
    Route::get('addons/{addon}', AddonShow::class)->name('hub.addons.show');
});

/**
 * Channel routes.
 */
Route::group([
    'middleware' => 'can:settings:core',
    'prefix'     => 'tags',
], function () {
    Route::get('/', TagsIndex::class)->name('hub.tags.index');
    // Route::get('channels/create', ChannelCreate::class)->name('hub.channels.create');
    Route::get('tags/{tag}', TagShow::class)->name('hub.tags.show');
});

 /**
  * Taxes.
  */
 Route::group([
     'middleware' => 'can:settings:core',
     'prefix'     => 'taxes',
 ], function () {
     Route::get('/tax-zones', TaxZonesIndex::class)->name('hub.taxes.index');
     Route::get('/tax-zones/create', TaxZoneCreate::class)->name('hub.taxes.create');
     Route::get('/tax-zones/{taxZone}', TaxZoneShow::class)->name('hub.taxes.show');
     Route::get('/tax-classes', TaxClassesIndex::class)->name('hub.taxes.tax-classes.index');
 });
