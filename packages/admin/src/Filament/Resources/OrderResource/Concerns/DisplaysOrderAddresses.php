<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Concerns;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Arr;
use Lunar\Models\Contracts\OrderAddress as OrderAddressContract;
use Lunar\Models\Country;
use Lunar\Models\OrderAddress;
use Lunar\Models\State;

trait DisplaysOrderAddresses
{
    public static function getDefaultShippingAddressInfoList(): Component
    {
        return self::getOrderAddressInfolistSchema('shipping');
    }

    public static function getShippingAddressInfolist(): Component
    {
        return self::callStaticLunarHook('extendShippingAddressInfolist', static::getDefaultShippingAddressInfoList());
    }

    public static function getDefaultBillingAddressInfoList(): Component
    {
        return self::getOrderAddressInfolistSchema('billing');
    }

    public static function getBillingAddressInfoList(): Component
    {
        return self::callStaticLunarHook('extendBillingAddressInfolist', static::getDefaultBillingAddressInfoList());
    }

    public static function getAddressEditSchema(): array
    {
        return self::callStaticLunarHook('extendAddressEditSchema', [
            Grid::make()
                ->schema([
                    TextInput::make('first_name')
                        ->label(__('lunarpanel::order.form.address.first_name.label'))
                        ->autocomplete(false)
                        ->maxLength(255)
                        ->required(),
                    TextInput::make('last_name')
                        ->label(__('lunarpanel::order.form.address.last_name.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                ]),
            TextInput::make('company_name')
                ->label(__('lunarpanel::order.form.address.company_name.label'))
                ->autocomplete(false)
                ->maxLength(255),
            TextInput::make('tax_identifier')
                ->label(__('lunarpanel::order.form.address.tax_identifier.label'))
                ->autocomplete(false)
                ->maxLength(255),
            Grid::make()
                ->schema([
                    TextInput::make('contact_phone')
                        ->label(__('lunarpanel::order.form.address.contact_phone.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                    TextInput::make('contact_email')
                        ->label(__('lunarpanel::order.form.address.contact_email.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                ]),
            TextInput::make('line_one')
                ->label(__('lunarpanel::order.form.address.line_one.label'))
                ->autocomplete(false)
                ->maxLength(255)
                ->required(),
            Grid::make()
                ->schema([
                    TextInput::make('line_two')
                        ->label(__('lunarpanel::order.form.address.line_two.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                    TextInput::make('line_three')
                        ->label(__('lunarpanel::order.form.address.line_three.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                ]),
            Grid::make(3)
                ->schema([
                    TextInput::make('city')
                        ->label(__('lunarpanel::order.form.address.city.label'))
                        ->maxLength(255)
                        ->autocomplete(false)
                        ->required(),
                    TextInput::make('state')
                        ->label(__('lunarpanel::order.form.address.state.label'))
                        ->autocomplete('state') // to disable browser input history while keeping datalist
                        ->datalist(fn ($get) => State::whereCountryId($get('country_id'))->pluck('name')->toArray())
                        ->maxLength(255),
                    TextInput::make('postcode')
                        ->label(__('lunarpanel::order.form.address.postcode.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                ]),
            Select::make('country_id')
                ->label(__('lunarpanel::order.form.address.country_id.label'))
                ->options(fn () => Country::get()->pluck('name', 'id'))
                ->live()
                ->searchable()
                ->required(),
        ]);
    }

    public static function getOrderAddressInfolistSchema(string $type): Section
    {
        $sameAsShipping = fn ($record) => $type == 'billing' && static::addressesMatch($record->shippingAddress, $record->billingAddress);

        $getAddress = fn ($record) => match ($type) {
            'billing' => $record->billingAddress,
            default => $record->shippingAddress,
        };

        return Section::make(__("lunarpanel::order.infolist.{$type}_address.label"))
            ->statePath($type.'Address')
            ->compact()
            ->headerActions([
                fn ($record) => static::getEditAddressAction($type)->hidden($sameAsShipping($record)),
            ])
            ->schema(fn ($record) => $sameAsShipping($record) ? [
                TextEntry::make('billing_matches_shipping')
                    ->hiddenLabel()
                    ->weight(FontWeight::SemiBold)
                    ->getStateUsing(fn () => __('lunarpanel::order.infolist.billing_matches_shipping.label')),

            ] : [
                TextEntry::make($type.'_address')
                    ->hiddenLabel()
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) use ($getAddress) {
                        $address = $getAddress($record);

                        if ($address?->id ?? false) {
                            return collect([
                                'company_name' => $address->company_name,
                                'tax_identifier' => $address->tax_identifier,
                                'fullName' => $address->fullName,
                                'line_one' => $address->line_one,
                                'line_two' => $address->line_two,
                                'line_three' => $address->line_three,
                                'city' => $address->city,
                                'state' => $address->state,
                                'postcode' => $address->postcode,
                                'country.name' => $address->country->name,
                            ])
                                ->filter(fn ($value, $key) => filled($value) || in_array($key, [
                                    'fullName', 'line_one', 'postcode', 'country.name',
                                ]))
                                ->toArray();
                        } else {
                            return __('lunarpanel::order.infolist.address_not_set.label');
                        }
                    }),
                TextEntry::make($type.'_phone')
                    ->hiddenLabel()
                    ->icon('heroicon-o-phone')
                    ->getStateUsing(fn ($record) => $getAddress($record)?->contact_phone ?? '-')
                    ->url(fn ($state) => $state !== '-' ? 'tel:'.$state : false)
                    ->color(fn ($state) => $state !== '-' ? Color::Sky : null)
                    ->iconColor(fn ($state) => $state !== '-' ? Color::Green : null),
                TextEntry::make($type.'_email')
                    ->hiddenLabel()
                    ->icon('heroicon-o-envelope')
                    ->getStateUsing(fn ($record) => $getAddress($record)?->contact_email ?? '-')
                    ->url(fn ($state) => $state !== '-' ? 'mailto:'.$state : false)
                    ->color(fn ($state) => $state !== '-' ? Color::Sky : null)
                    ->iconColor(fn ($state) => $state !== '-' ? Color::Amber : null),
            ]);
    }

    private static function addressesMatch(?OrderAddressContract $original = null, ?OrderAddressContract $comparison = null): bool
    {
        if (! $original || ! $comparison) {
            return false;
        }

        $fieldsToCheck = Arr::except(
            $comparison->getAttributes(),
            ['id', 'created_at', 'updated_at', 'order_id', 'type', 'meta', 'shipping_option']
        );

        // Is the same until proven otherwise
        $isSame = true;
        foreach ($fieldsToCheck as $field => $value) {
            if ($original->getAttribute($field) != $value) {
                $isSame = false;
            }
        }

        return $isSame;
    }

    public static function getEditAddressAction(string $type): Action
    {
        return Action::make("edit_{$type}_address")
            ->modalHeading(__("lunarpanel::order.infolist.{$type}_address.label"))
            ->modalWidth('2xl')
            ->label(__('lunarpanel::order.action.edit_address.label'))
            ->button()
            ->fillForm(fn ($record) => match ($type) {
                'shipping' => $record->shippingAddress?->toArray() ?: [],
                'billing' => $record->billingAddress?->toArray() ?: [],
                default => []
            })
            ->schema(function () {
                return static::getAddressEditSchema();
            })
            ->action(function (Action $action, $record, $data) use ($type) {
                $addressType = match ($type) {
                    'shipping' => 'shippingAddress',
                    'billing' => 'billingAddress',
                    default => null,
                };

                if (blank($addressType)) {
                    $action->failureNotificationTitle(__('lunarpanel::order.action.edit_address.notification.error'));
                    $action->failure();

                    $action->halt();

                    return;
                }

                $oldData = $record->$addressType?->toArray() ?: [];
                $formFields = array_keys($data);

                if (! $record->$addressType) {
                    $record->$addressType = (new OrderAddress)->forceFill($data);
                    $record->$addressType->type = $type;
                }

                $record->$addressType->order_id = $record->id;
                $record->$addressType->id ? $record->$addressType->update($data) : $record->$addressType->save();

                $record->$addressType->order_id = $record->id;
                $record->$addressType->update($data);
                $refreshed = $record->$addressType->refresh();

                // should this be in Core as model observer?
                $diff = collect($oldData)->only($formFields)->diff(collect($refreshed->toArray())->only($formFields));
                if ($diff->count()) {
                    activity()
                        ->causedBy(Filament::auth()->user())
                        ->performedOn($record)
                        ->event('order-address-update')
                        ->withProperties([
                            'fields' => $formFields,
                            'type' => $addressType,
                            'new' => $record->$addressType->toArray(),
                            'previous' => $oldData,
                        ])->log('order-address-update');
                }

                $action->successNotificationTitle(__("lunarpanel::order.action.edit_address.notification.{$type}_address.saved"));

                $action->success();
            })
            ->slideOver();
    }
}
