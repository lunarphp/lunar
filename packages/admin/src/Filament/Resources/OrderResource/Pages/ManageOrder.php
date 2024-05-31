<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Awcodes\Shout\Components\Shout;
use Awcodes\Shout\Components\ShoutEntry;
use Closure;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Computed;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Actions\Orders\UpdateStatusAction;
use Lunar\Admin\Support\Actions\PdfDownload;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Infolists\Components\Livewire;
use Lunar\Admin\Support\Infolists\Components\Tags;
use Lunar\Admin\Support\Infolists\Components\Timeline;
use Lunar\Admin\Support\Infolists\Components\Transaction as InfolistsTransaction;
use Lunar\Admin\Support\OrderStatus;
use Lunar\Admin\Support\Pages\BaseViewRecord;
use Lunar\DataTypes\Price;
use Lunar\Models\Country;
use Lunar\Models\State;
use Lunar\Models\Tag;
use Lunar\Models\Transaction;

/**
 * @property \Lunar\Models\Order $record
 * @property \Illuminate\Support\Collection $transactions
 * @property string $paymentStatus
 * @property bool $requiresCapture
 * @property int $captureTotal
 * @property int $refundTotal
 * @property int $intentTotal
 * @property \Illuminate\Support\Collection $intents
 * @property \Illuminate\Support\Collection $charges
 * @property \Illuminate\Support\Collection $refunds
 * @property float $availableToRefund
 * @property bool $canBeRefunded
 */
class ManageOrder extends BaseViewRecord
{
    use CanDispatchActivityUpdated;

    protected static string $resource = OrderResource::class;

    protected static string $view = 'lunarpanel::resources.order-resource.pages.manage-order';

    protected ?string $maxContentWidth = 'screen-2xl';

    public function getBreadcrumb(): string
    {
        return __('lunarpanel::order.breadcrumb.manage');
    }

    public function getTitle(): string|Htmlable
    {
        $label = static::getResource()::getModelLabel();

        return "{$label} #".$this->record->id;
    }

    public static function getOrderLinesTable(): Livewire
    {
        return Livewire::make('lines')
            ->content(OrderResource\Pages\Components\OrderItemsTable::class);
    }

    public static function getShippingInfolist(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make()
            ->schema([
                Infolists\Components\RepeatableEntry::make('shippingLines')
                    ->hiddenLabel()
                    ->contained(false)
                    ->columns(2)
                    ->columnSpan(12)
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->icon('heroicon-s-truck')
                            ->html()
                            ->iconPosition(IconPosition::Before)
                            ->hiddenLabel(),
                        Infolists\Components\TextEntry::make('sub_total')
                            ->hiddenLabel()
                            ->alignEnd()
                            ->formatStateUsing(fn ($state) => $state->formatted),
                        Infolists\Components\TextEntry::make('notes')
                            ->hidden(
                                fn ($state) => ! $state
                            )
                            ->placeholder(
                                __('lunarpanel::order.infolist.notes.placeholder')
                            ),
                    ]),
            ]);
    }

    public static function getOrderTotalsInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Section::make()
            ->schema([
                Infolists\Components\Grid::make()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\Grid::make()
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('shippingAddress.delivery_instructions')
                                    ->label(__('lunarpanel::order.infolist.delivery_instructions.label'))
                                    ->hidden(fn ($state) => blank($state)),
                                Infolists\Components\TextEntry::make('notes')
                                    ->label(__('lunarpanel::order.infolist.notes.label'))
                                    ->placeholder(__('lunarpanel::order.infolist.notes.placeholder')),
                            ]),
                        Infolists\Components\Grid::make()
                            ->columns(1)
                            ->columnSpan(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('sub_total')
                                    ->label(__('lunarpanel::order.infolist.sub_total.label'))
                                    ->inlineLabel()
                                    ->alignEnd()
                                    ->formatStateUsing(fn ($state) => $state->formatted),
                                Infolists\Components\TextEntry::make('discount_total')
                                    ->label(__('lunarpanel::order.infolist.discount_total.label'))
                                    ->inlineLabel()
                                    ->alignEnd()
                                    ->formatStateUsing(fn ($state) => $state->formatted),
                                Infolists\Components\Group::make()
                                    ->statePath('shipping_breakdown')
                                    ->schema(function ($state) {
                                        $shipping = [];
                                        foreach ($state->items ?? [] as $shippingIndex => $shippingItem) {
                                            $shipping[] = Infolists\Components\TextEntry::make('shipping_'.$shippingIndex)
                                                ->label(fn () => $shippingItem->name)
                                                ->inlineLabel()
                                                ->alignEnd()
                                                ->state(fn () => $shippingItem->price->formatted);
                                        }

                                        return $shipping;
                                    }),

                                Infolists\Components\Group::make()
                                    ->statePath('tax_breakdown')
                                    ->schema(function ($state) {
                                        $taxes = [];
                                        foreach ($state->amounts ?? [] as $taxIndex => $tax) {
                                            $taxes[] = Infolists\Components\TextEntry::make('tax_'.$taxIndex)
                                                ->label(fn () => $tax->description)
                                                ->inlineLabel()
                                                ->alignEnd()
                                                ->state(fn () => $tax->price->formatted);
                                        }

                                        return $taxes;
                                    }),
                                Infolists\Components\TextEntry::make('total')
                                    ->label(fn () => new HtmlString('<b>'.__('lunarpanel::order.infolist.total.label').'</b>'))
                                    ->inlineLabel()
                                    ->alignEnd()
                                    ->weight(FontWeight::Bold)
                                    ->formatStateUsing(fn ($state) => $state->formatted),
                                Infolists\Components\TextEntry::make('paid')
                                    ->label(fn () => __('lunarpanel::order.infolist.paid.label'))
                                    ->inlineLabel()
                                    ->alignEnd()
                                    ->weight(FontWeight::SemiBold)
                                    ->getStateUsing(function ($record) {
                                        $paid = $record->transactions()
                                            ->whereType('capture')
                                            ->whereSuccess(true)
                                            ->get()
                                            ->sum('amount.value');

                                        return (new Price($paid, $record->currency))->formatted;
                                    }),
                                Infolists\Components\TextEntry::make('refund')
                                    ->label(fn () => __('lunarpanel::order.infolist.refund.label'))
                                    ->inlineLabel()
                                    ->alignEnd()
                                    ->color('warning')
                                    ->weight(FontWeight::SemiBold)
                                    ->getStateUsing(function ($record) {
                                        $paid = $record->transactions()
                                            ->whereType('refund')
                                            ->get()
                                            ->sum('amount.value');

                                        return (new Price($paid, $record->currency))->formatted;
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function getTransactionsInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Section::make('transactions')
            ->heading(__('lunarpanel::order.infolist.transactions.label'))
            ->compact()
            ->collapsed(fn ($state) => filled($state))
            ->collapsible(fn ($state) => filled($state))
            ->schema([
                Infolists\Components\RepeatableEntry::make('transactions')
                    ->hiddenLabel()
                    ->placeholder(__('lunarpanel::order.infolist.transactions.placeholder'))
                    ->getStateUsing(fn ($record) => $record->transactions)
                    ->contained(false)
                    ->schema([
                        InfolistsTransaction::make('transactions'),
                    ]),
            ]);
    }

    public static function getTimelineInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Grid::make()
            ->schema([
                Timeline::make('timeline')
                    ->label(__('lunarpanel::order.infolist.timeline.label')),
            ]);
    }

    public static function getOrderSummaryInfolist(): Infolists\Components\Component
    {
        return Infolists\Components\Section::make()
            ->compact()
            ->inlineLabel()
            ->schema([
                Infolists\Components\TextEntry::make('new_customer')
                    ->label(__('lunarpanel::order.infolist.new_returning.label'))
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => __('lunarpanel::order.infolist.'.($state ? 'new' : 'returning').'_customer.label')),
                Infolists\Components\TextEntry::make('status')
                    ->label(__('lunarpanel::order.infolist.status.label'))
                    ->formatStateUsing(fn ($state) => OrderStatus::getLabel($state))
                    ->alignEnd()
                    ->color(fn ($state) => OrderStatus::getColor($state))
                    ->badge(),
                Infolists\Components\TextEntry::make('reference')
                    ->label(__('lunarpanel::order.infolist.reference.label'))
                    ->alignEnd()
                    ->icon('heroicon-o-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->copyable(),
                Infolists\Components\TextEntry::make('customer_reference')
                    ->label(__('lunarpanel::order.infolist.customer_reference.label'))
                    ->alignEnd()
                    ->icon('heroicon-o-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->copyable(),
                Infolists\Components\TextEntry::make('channel.name')
                    ->label(__('lunarpanel::order.infolist.channel.label'))
                    ->alignEnd(),
                Infolists\Components\TextEntry::make('created_at')
                    ->label(__('lunarpanel::order.infolist.date_created.label'))
                    ->alignEnd()
                    ->dateTime('Y-m-d h:i a')
                    ->visible(fn ($record) => ! $record->placed_at),
                Infolists\Components\TextEntry::make('placed_at')
                    ->label(__('lunarpanel::order.infolist.date_placed.label'))
                    ->alignEnd()
                    ->dateTime('Y-m-d h:i a')
                    ->placeholder('-'),
            ]);
    }

    public function getDefaultInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        ShoutEntry::make('requires_capture')
                            ->type('danger')
                            ->content(__('lunarpanel::order.infolist.alert.requires_capture'))
                            ->visible(fn () => $this->requiresCapture),
                        ShoutEntry::make('requires_capture')
                            ->state(fn () => $this->paymentStatus)
                            ->icon(fn ($state) => match ($state) {
                                'refunded' => FilamentIcon::resolve('lunar::exclamation-circle'),
                                default => null
                            })
                            ->color(fn (ShoutEntry $component, $state) => match ($state) {
                                'partial-refund' => 'info',
                                'refunded' => 'danger',
                                default => null
                            })->content(fn ($state) => match ($state) {
                                'partial-refund' => __('lunarpanel::order.infolist.alert.partially_refunded'),
                                'refunded' => __('lunarpanel::order.infolist.alert.refunded') ,
                                default => null
                            })
                            ->visible(fn ($state) => in_array($state, ['partial-refund', 'refunded'])),

                        static::getShippingInfolist(),
                        static::getOrderLinesTable(),
                        static::getOrderTotalsInfolist(),
                        static::getTransactionsInfolist(),
                        static::getTimelineInfolist(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('customer')
                            ->hidden(fn ($state) => blank($state?->id))
                            ->formatStateUsing(fn ($state) => $state->fullName)
                            ->weight(FontWeight::SemiBold)
                            ->size(TextEntrySize::Large)
                            ->hiddenLabel()
                            ->suffixAction(fn ($state) => Action::make('view customer')
                                ->color('gray')
                                ->button()
                                ->size(ActionSize::ExtraSmall)
                                ->url(CustomerResource::getUrl('edit', ['record' => $state->id]))),
                        static::getOrderSummaryInfolist(),
                        $this->getOrderAddressInfolistSchema('shipping'),
                        $this->getOrderAddressInfolistSchema('billing'),
                        Infolists\Components\Section::make('tags')
                            ->heading(__('lunarpanel::order.infolist.tags.label'))
                            ->headerActions([
                                fn ($record) => $this->getEditTagsActions(),
                            ])
                            ->compact()
                            ->schema([
                                Tags::make(''),
                            ]),

                        Infolists\Components\Section::make('additional_info')
                            ->heading(__('lunarpanel::order.infolist.additional_info.label'))
                            ->compact()
                            ->statePath('meta')
                            ->schema(fn ($state) => blank($state) ? [
                                Infolists\Components\TextEntry::make('no_additional_info')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn () => __('lunarpanel::order.infolist.no_additional_info.label')),
                            ] : collect($state)
                                ->map(function ($value, $key) {
                                    if (! is_array($value)) {
                                        return Infolists\Components\TextEntry::make('meta_'.$key)
                                            ->state($value)
                                            ->label($key)
                                            ->copyable()
                                            ->limit(50)->tooltip(function (Infolists\Components\TextEntry $component): ?string {
                                                $state = $component->getState();
                                                if (strlen($state) <= $component->getCharacterLimit()) {
                                                    return null;
                                                }

                                                return $state;
                                            });
                                    } else {
                                        return Infolists\Components\KeyValueEntry::make('meta_'.$key)
                                            ->state($value);
                                    }
                                })
                                ->toArray()),

                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    /**
     * Returns whether this order still requires capture.
     */
    #[Computed]
    public function requiresCapture(): bool
    {
        $captures = $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'capture';
        })->count();

        $intents = $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'intent';
        })->count();

        if (! $intents) {
            return false;
        }

        return ! $captures;
    }

    /**
     * Return the order transactions.
     */
    #[Computed]
    public function transactions(): \Illuminate\Support\Collection
    {
        return $this->record->transactions()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Return whether this order is partially refunded.
     */
    #[Computed]
    public function paymentStatus(): string
    {
        $total = $this->intentTotal ?: $this->captureTotal;

        if (! $total) {
            return 'offline';
        }

        if (
            ($this->refundTotal && $this->refundTotal < $total) ||
            ($this->captureTotal && $this->captureTotal < $this->intentTotal)
        ) {
            return 'partial-refund';
        }

        if ($this->refundTotal >= $total) {
            return 'refunded';
        }

        if ($this->captureTotal >= $this->intentTotal) {
            return 'captured';
        }

        return 'uncaptured';
    }

    /**
     * Return the total amount captured.
     */
    #[Computed]
    public function captureTotal(): int
    {
        return $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'capture' && $transaction->success;
        })->sum('amount.value');
    }

    /**
     * Return the total amount refunded.
     */
    #[Computed()]
    public function refundTotal(): int
    {
        return $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'refund' && $transaction->success;
        })->sum('amount.value');
    }

    /**
     * Return the total amount intent.
     */
    #[Computed]
    public function intentTotal(): int
    {
        return $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'intent' && $transaction->success;
        })->sum('amount.value');
    }

    private function isShippingEqualsBilling($shippingAddress, $billingAddress): bool
    {
        if (! $shippingAddress || ! $billingAddress) {
            return false;
        }

        $fieldsToCheck = Arr::except(
            $billingAddress->getAttributes(),
            ['id', 'created_at', 'updated_at', 'order_id', 'type', 'meta']
        );

        // Is the same until proven otherwise
        $isSame = true;

        foreach ($fieldsToCheck as $field => $value) {
            if ($shippingAddress->getAttribute($field) != $value) {
                $isSame = false;
            }
        }

        return $isSame;
    }

    public function getOrderAddressInfolistSchema(string $type)
    {
        $sameAsShipping = fn ($record) => $type == 'billing' && $this->isShippingEqualsBilling($record->shippingAddress, $record->billingAddress);

        $getAddress = fn ($record) => match ($type) {
            'billing' => $record->billingAddress,
            'shipping' => $record->shippingAddress,
            default => null,
        };

        return Infolists\Components\Section::make(__("lunarpanel::order.infolist.{$type}_address.label"))
            ->statePath($type.'Address')
            ->compact()
            ->headerActions([
                fn ($record) => $this->getEditAddressAction($type)->hidden($sameAsShipping($record)),
            ])
            ->schema(fn ($record) => $sameAsShipping($record) ? [
                Infolists\Components\TextEntry::make('billing_matches_shipping')
                    ->hiddenLabel()
                    ->weight(FontWeight::SemiBold)
                    ->getStateUsing(fn () => __('lunarpanel::order.infolist.billing_matches_shipping.label')),

            ] : [
                Infolists\Components\TextEntry::make($type.'_address')
                    ->hiddenLabel()
                    ->listWithLineBreaks()
                    ->getStateUsing(function ($record) use ($getAddress) {
                        $address = $getAddress($record);

                        if ($address?->id ?? false) {
                            return collect([
                                'company_name' => $address->company_name,
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
                Infolists\Components\TextEntry::make($type.'_phone')
                    ->hiddenLabel()
                    ->icon('heroicon-o-phone')
                    ->getStateUsing(fn ($record) => $getAddress($record)?->contact_phone ?? '-')
                    ->url(fn ($state) => $state !== '-' ? 'tel:'.$state : false)
                    ->color(fn ($state) => $state !== '-' ? Color::Sky : null)
                    ->iconColor(fn ($state) => $state !== '-' ? Color::Green : null),
                Infolists\Components\TextEntry::make($type.'_email')
                    ->hiddenLabel()
                    ->icon('heroicon-o-envelope')
                    ->getStateUsing(fn ($record) => $getAddress($record)?->contact_email ?? '-')
                    ->url(fn ($state) => $state !== '-' ? 'mailto:'.$state : false)
                    ->color(fn ($state) => $state !== '-' ? Color::Sky : null)
                    ->iconColor(fn ($state) => $state !== '-' ? Color::Amber : null),
            ]);
    }

    public function getEditTagsActions(): Action
    {
        return Action::make('edit_tags')
            ->modalHeading(__('lunarpanel::order.infolist.tags.label'))
            ->modalWidth('2xl')
            ->label(__('lunarpanel::order.action.edit_tags.label'))
            ->button()
            ->fillForm(fn ($record): array => [
                'tags' => $record->tags,
            ])
            ->form(function () {
                return [
                    TagsComponent::make('')
                        ->suggestions(Tag::all()->pluck('value')->all()),
                ];
            })->action(function (Action $action, $record, $data) {
                $this->dispatchActivityUpdated();
            });
    }

    public function getEditAddressAction(string $type): Action
    {
        return Action::make("edit_{$type}_address")
            ->modalHeading(__("lunarpanel::order.infolist.{$type}_address.label"))
            ->modalWidth('2xl')
            ->label(__('lunarpanel::order.action.edit_address.label'))
            ->button()
            ->fillForm(fn ($record) => match ($type) {
                'shipping' => $record->shippingAddress->toArray(),
                'billing' => $record->billingAddress->toArray(),
                default => []
            })
            ->form(function () {
                return [
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('first_name')
                                ->label(__('lunarpanel::order.form.address.first_name.label'))
                                ->autocomplete(false)
                                ->maxLength(255)
                                ->required(),
                            Forms\Components\TextInput::make('last_name')
                                ->label(__('lunarpanel::order.form.address.last_name.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                        ]),
                    Forms\Components\TextInput::make('company_name')
                        ->label(__('lunarpanel::order.form.address.company_name.label'))
                        ->autocomplete(false)
                        ->maxLength(255),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('contact_phone')
                                ->label(__('lunarpanel::order.form.address.contact_phone.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('contact_email')
                                ->label(__('lunarpanel::order.form.address.contact_email.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                        ]),
                    Forms\Components\TextInput::make('line_one')
                        ->label(__('lunarpanel::order.form.address.line_one.label'))
                        ->autocomplete(false)
                        ->maxLength(255)
                        ->required(),
                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('line_two')
                                ->label(__('lunarpanel::order.form.address.line_two.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('line_three')
                                ->label(__('lunarpanel::order.form.address.line_three.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('city')
                                ->label(__('lunarpanel::order.form.address.city.label'))
                                ->maxLength(255)
                                ->autocomplete(false)
                                ->required(),
                            Forms\Components\TextInput::make('state')
                                ->label(__('lunarpanel::order.form.address.state.label'))
                                ->autocomplete('state') // to disable browser input history while keeping datalist
                                ->datalist(fn ($get) => State::whereCountryId($get('country_id'))->pluck('name')->toArray())
                                ->maxLength(255),
                            Forms\Components\TextInput::make('postcode')
                                ->label(__('lunarpanel::order.form.address.postcode.label'))
                                ->autocomplete(false)
                                ->maxLength(255),
                        ]),
                    Forms\Components\Select::make('country_id')
                        ->label(__('lunarpanel::order.form.address.country_id.label'))
                        ->options(fn () => Country::get()->pluck('name', 'id'))
                        ->live()
                        ->searchable()
                        ->required(),
                ];
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

                $oldData = $record->$addressType->toArray();
                $formFields = array_keys($data);

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

                    $this->dispatchActivityUpdated();
                }

                $action->successNotificationTitle(__("lunarpanel::order.action.edit_address.notification.{$type}_address.saved"));

                $action->success();
            })
            ->slideOver();
    }

    protected function getDefaultHeaderActions(): array
    {
        return [
            $this->getCaptureAction(),
            $this->getRefundAction(),
            UpdateStatusAction::make('update_status')
                ->after(
                    function () {
                        $this->dispatchActivityUpdated();
                    }
                ),
            PdfDownload::make('download_pdf')
                ->pdfView('lunarpanel::pdf.order')
                ->label(__('lunarpanel::order.action.download_order_pdf.label'))
                ->filename(function ($record) {
                    return "Order-{$record->reference}.pdf";
                }),
        ];
    }

    protected function getRefundAction(): Actions\Action
    {
        return Actions\Action::make('refund')
            ->label(__('lunarpanel::order.action.refund_payment.label'))
            ->modalSubmitActionLabel(__('lunarpanel::order.action.refund_payment.label'))
            ->icon('heroicon-o-backward')
            ->form(fn () => [

                Forms\Components\Select::make('transaction')
                    ->label(__('lunarpanel::order.form.transaction.label'))
                    ->required()
                    ->default(fn () => $this->charges->first()->id)
                    ->options(fn () => $this->charges
                        ->mapWithKeys(fn ($charge) => [
                            $charge->id => "{$charge->amount->formatted} - {$charge->driver} // {$charge->reference}",
                        ]))
                    ->live(),

                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->label(__('lunarpanel::order.form.amount.label'))
                    ->suffix(fn ($record) => $record->currency->code)
                    ->default(fn ($record) => number_format($this->availableToRefund / $record->currency->factor, $record->currency->decimal_places, '.', ''))
                    ->live()
                    ->autocomplete(false)
                    ->minValue(
                        1 / $this->getRecord()->currency->factor
                    )
                    ->numeric(),

                Forms\Components\Textarea::make('notes')
                    ->label(__('lunarpanel::order.form.notes.label'))
                    ->autocomplete(false)
                    ->maxLength(255),

                Forms\Components\Toggle::make('confirm')
                    ->label(__('lunarpanel::order.form.confirm.label'))
                    ->helperText(__('lunarpanel::order.form.confirm.hint.refund'))
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value !== true) {
                                    $fail(__('lunarpanel::order.form.confirm.alert'));
                                }
                            };
                        },
                    ]),
            ])
            ->action(function ($data, $record, Actions\Action $action) {
                $transaction = Transaction::findOrFail($data['transaction']);

                $response = $transaction->refund(bcmul($data['amount'], $record->currency->factor), $data['notes']);

                if (! $response->success) {
                    $action->failureNotification(
                        fn () => Notification::make('refund_failure')->color('danger')->title($response->message)
                    );

                    $action->failure();

                    $action->halt();

                    return;
                }

                $action->success();
            })
            ->successNotificationTitle(__('lunarpanel::order.action.refund_payment.notification.success'))
            ->failureNotificationTitle(__('lunarpanel::order.action.refund_payment.notification.error'))
            ->color('warning')
            ->visible($this->charges->count() && $this->canBeRefunded);
    }

    #[Computed]
    public function charges(): \Illuminate\Support\Collection
    {
        return $this->record->transactions()->whereType('capture')->whereSuccess(true)->get();
    }

    #[Computed]
    public function refunds(): \Illuminate\Support\Collection
    {
        return $this->record->transactions()->whereType('refund')->whereSuccess(true)->get();
    }

    #[Computed]
    public function availableToRefund(): float
    {
        return $this->charges->sum('amount.value') - $this->refunds->sum('amount.value');
    }

    #[Computed]
    public function canBeRefunded(): bool
    {
        return $this->availableToRefund > 0;
    }

    protected function getCaptureAction(): Actions\Action
    {
        return Actions\Action::make('capture')
            ->label(__('lunarpanel::order.action.capture_payment.label'))
            ->modalSubmitActionLabel(__('lunarpanel::order.action.capture_payment.label'))
            ->icon('heroicon-o-credit-card')
            ->modalWidth('lg')
            ->form(fn () => [
                Forms\Components\Select::make('transaction')
                    ->label(__('lunarpanel::order.form.transaction.label'))
                    ->required()
                    ->default(fn () => $this->intents->first()->id)
                    ->options(fn () => $this->intents
                        ->mapWithKeys(fn ($intent) => [
                            $intent->id => "{$intent->amount->formatted} - {$intent->driver}",
                        ]))
                    ->live(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->label(__('lunarpanel::order.form.amount.label'))
                    ->suffix(fn ($record) => $record->currency->code)
                    ->default(fn ($record) => number_format($record->total->decimal, $record->currency->decimal_places, '.', ''))
                    ->live()
                    ->autocomplete(false)
                    ->minValue(1)
                    ->helperText(function (Forms\Components\TextInput $component, $get, $state) {
                        $transaction = Transaction::findOrFail($get('transaction'));

                        $message = $transaction->amount->decimal > $state ? __('lunarpanel::order.form.amount.hint.less_than_total') : null;

                        if (blank($message)) {
                            return null;
                        }

                        return Shout::make('alert')
                            ->container($component->getContainer())
                            ->type('danger')
                            ->icon(FilamentIcon::resolve('lunar::exclamation-circle'))
                            ->content($message);
                    })
                    ->numeric(),
                Forms\Components\Toggle::make('confirm')
                    ->label(__('lunarpanel::order.form.confirm.label'))
                    ->helperText(__('lunarpanel::order.form.confirm.hint.capture'))
                    ->rules([
                        function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if ($value !== true) {
                                    $fail(__('lunarpanel::order.form.confirm.alert'));
                                }
                            };
                        },
                    ]),
            ])
            ->action(function ($data, $record, Actions\Action $action) {
                $transaction = Transaction::findOrFail($data['transaction']);

                $response = $transaction->capture(bcmul($data['amount'], $record->currency->factor));

                if (! $response->success) {
                    $action->failureNotification(fn () => $response->message);

                    $action->failure();

                    $action->halt();

                    return;
                }

                $action->success();
            })
            ->successNotificationTitle(__('lunarpanel::order.action.capture_payment.notification.success'))
            ->failureNotificationTitle(__('lunarpanel::order.action.capture_payment.notification.error'))
            ->visible($this->requiresCapture && $this->intents->count());
    }

    #[Computed]
    public function intents(): \Illuminate\Support\Collection
    {
        return $this->record->transactions()->whereType('intent')->whereSuccess(true)->get();
    }
}
