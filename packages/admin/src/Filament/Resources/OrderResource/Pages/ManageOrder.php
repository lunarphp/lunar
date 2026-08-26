<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Awcodes\Shout\Components\Shout;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderAddresses;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderSummary;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderTimeline;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderTotals;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysShippingInfo;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysTransactions;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable;
use Lunar\Admin\Support\Actions\Orders\UpdateStatusAction;
use Lunar\Admin\Support\Actions\PdfDownload;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Infolists\Components\Livewire;
use Lunar\Admin\Support\Infolists\Components\Tags;
use Lunar\Admin\Support\Pages\BaseViewRecord;
use Lunar\Models\Order;
use Lunar\Models\Tag;
use Lunar\Models\Transaction;

/**
 * @property Order $record
 * @property Collection $transactions
 * @property string $paymentStatus
 * @property bool $requiresCapture
 * @property int $captureTotal
 * @property int $refundTotal
 * @property int $intentTotal
 * @property Collection $intents
 * @property Collection $charges
 * @property Collection $refunds
 * @property float $availableToRefund
 * @property bool $canBeRefunded
 */
class ManageOrder extends BaseViewRecord
{
    use CallsHooks;
    use CanDispatchActivityUpdated;
    use DisplaysOrderAddresses;
    use DisplaysOrderSummary;
    use DisplaysOrderTimeline;
    use DisplaysOrderTotals;
    use DisplaysShippingInfo;
    use DisplaysTransactions;

    protected static string $resource = OrderResource::class;

    protected string $view = 'lunarpanel::resources.order-resource.pages.manage-order';

    protected Width|string|null $maxContentWidth = 'screen-2xl';

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
            ->content(OrderItemsTable::class);
    }

    public static function getInfolistSchema(): array
    {
        return self::callStaticLunarHook('extendInfolistSchema', [
            static::getShippingInfolist(),
            static::getOrderLinesTable(),
            static::getOrderTotalsInfolist(),
            static::getTransactionsInfolist(),
            static::getTimelineInfolist(),
        ]);
    }

    public static function getInfolistAsideSchema(): array
    {
        return self::callStaticLunarHook('extendInfolistAsideSchema', [
            static::getCustomerEntry(),
            static::getOrderSummaryInfolist(),
            static::getShippingAddressInfolist(),
            static::getBillingAddressInfoList(),
            static::getTagsSection(),
            static::getAdditionalInfoSection(),
        ]);
    }

    public static function getDefaultCustomerEntry(): Entry
    {
        return TextEntry::make('customer')
            ->hidden(fn ($state) => blank($state?->id))
            ->formatStateUsing(fn ($state) => $state->fullName)
            ->weight(FontWeight::SemiBold)
            ->size(TextSize::Large)
            ->hiddenLabel()
            ->suffixAction(fn ($state) => Action::make('view customer')
                ->color('gray')
                ->button()
                ->size(Size::ExtraSmall)
                ->url(CustomerResource::getUrl('edit', ['record' => $state->id])));
    }

    public static function getCustomerEntry(): Component
    {
        return self::callStaticLunarHook('extendCustomerEntry', static::getDefaultCustomerEntry());
    }

    public static function getDefaultTagsSection(): Section
    {
        return Section::make('tags')
            ->heading(__('lunarpanel::order.infolist.tags.label'))
            ->headerActions([
                fn ($record) => static::getEditTagsActions(),
            ])
            ->compact()
            ->schema([
                Tags::make('tags'),
            ]);
    }

    public static function getTagsSection(): Component
    {
        return self::callStaticLunarHook('extendTagsSection', static::getDefaultTagsSection());
    }

    public static function getDefaultAdditionalInfoSection(): Section
    {
        return Section::make('additional_info')
            ->heading(__('lunarpanel::order.infolist.additional_info.label'))
            ->compact()
            ->statePath('meta')
            ->schema(function ($record) {
                $meta = $record?->meta;

                if (blank($meta)) {
                    return [
                        TextEntry::make('no_additional_info')
                            ->hiddenLabel()
                            ->getStateUsing(fn () => __('lunarpanel::order.infolist.no_additional_info.label')),
                    ];
                }

                return collect($meta)
                    ->map(function ($value, $key) {
                        if (is_array($value)) {
                            return KeyValueEntry::make('meta_'.$key)->getStateUsing(fn () => $value);
                        }

                        return TextEntry::make('meta_'.$key)
                            ->getStateUsing(fn () => is_bool($value)
                                ? __($value ? 'lunarpanel::global.yes' : 'lunarpanel::global.no')
                                : $value)
                            ->label($key)
                            ->copyable()
                            ->limit(50)->tooltip(function (TextEntry $component): ?string {
                                $state = $component->getState();
                                if (strlen($state) <= $component->getCharacterLimit()) {
                                    return null;
                                }

                                return $state;
                            });
                    })
                    ->toArray();
            });
    }

    public static function getAdditionalInfoSection(): Component
    {
        return self::callStaticLunarHook('extendAdditionalInfoSection', static::getDefaultAdditionalInfoSection());
    }

    public function getDefaultInfolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Group::make()->key('shouts')->schema([
                            Shout::make('requires_capture')
                                ->type('danger')
                                ->content(__('lunarpanel::order.infolist.alert.requires_capture'))
                                ->visible(fn () => $this->requiresCapture),
                            Shout::make('partially_refunded')
                                ->key('partially_refunded_notice')
                                ->icon(fn () => match ($this->paymentStatus) {
                                    'refunded' => FilamentIcon::resolve('lunar::exclamation-circle'),
                                    default => null
                                })
                                ->color(fn () => match ($this->paymentStatus) {
                                    'partial-refund' => 'info',
                                    'refunded' => 'danger',
                                    default => null
                                })->content(fn () => match ($this->paymentStatus) {
                                    'partial-refund' => __('lunarpanel::order.infolist.alert.partially_refunded'),
                                    'refunded' => __('lunarpanel::order.infolist.alert.refunded'),
                                    default => null
                                })
                                ->visible(fn () => in_array($this->paymentStatus, ['partial-refund', 'refunded'])),
                        ]),
                        ...static::getInfolistSchema(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema(static::getInfolistAsideSchema())
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
    public function transactions(): Collection
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

    public static function getEditTagsActions(): Action
    {
        return Action::make('edit_tags')
            ->modalHeading(__('lunarpanel::order.infolist.tags.label'))
            ->modalWidth('2xl')
            ->label(__('lunarpanel::order.action.edit_tags.label'))
            ->button()
            ->fillForm(fn ($record): array => [
                'tags' => $record->tags,
            ])
            ->schema(function () {
                return [
                    TagsComponent::make('')
                        ->splitKeys(['Tab', ','])
                        ->label(__('lunarpanel::order.action.edit_tags.form.tags.label'))
                        ->helperText(__('lunarpanel::order.action.edit_tags.form.tags.helper_text'))
                        ->suggestions(Tag::all()->pluck('value')->all()),
                ];
            })->action(function (Action $action, $record, $data) {
                //                $this->dispatchActivityUpdated();
            });
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

    protected function getRefundAction(): Action
    {
        return Action::make('refund')
            ->label(__('lunarpanel::order.action.refund_payment.label'))
            ->modalSubmitActionLabel(__('lunarpanel::order.action.refund_payment.label'))
            ->icon('heroicon-o-backward')
            ->schema(fn () => [

                Select::make('transaction')
                    ->label(__('lunarpanel::order.form.transaction.label'))
                    ->required()
                    ->default(fn () => $this->charges->first()->id)
                    ->options(fn () => $this->charges
                        ->mapWithKeys(fn ($charge) => [
                            $charge->id => "{$charge->amount->formatted} - {$charge->driver} // {$charge->reference}",
                        ]))
                    ->live(),

                TextInput::make('amount')
                    ->required()
                    ->label(__('lunarpanel::order.form.amount.label'))
                    ->suffix(fn ($record) => $record->currency->code)
                    ->default(fn ($record) => number_format($this->availableToRefund / $record->currency->factor, $record->currency->decimal_places, '.', ''))
                    ->live()
                    ->autocomplete(false)
                    ->minValue(
                        fn ($record) => 1 / $record->currency->factor
                    )
                    ->numeric(),

                Textarea::make('notes')
                    ->label(__('lunarpanel::order.form.notes.label'))
                    ->autocomplete(false)
                    ->maxLength(255),

                Toggle::make('confirm')
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
            ->action(function ($data, $record, Action $action) {
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
    public function charges(): Collection
    {
        return $this->record->transactions()->whereType('capture')->whereSuccess(true)->get();
    }

    #[Computed]
    public function refunds(): Collection
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

    protected function getCaptureAction(): Action
    {
        return Action::make('capture')
            ->label(__('lunarpanel::order.action.capture_payment.label'))
            ->modalSubmitActionLabel(__('lunarpanel::order.action.capture_payment.label'))
            ->icon('heroicon-o-credit-card')
            ->modalWidth('lg')
            ->schema(fn () => [
                Select::make('transaction')
                    ->label(__('lunarpanel::order.form.transaction.label'))
                    ->required()
                    ->default(fn () => $this->intents->first()->id)
                    ->options(fn () => $this->intents
                        ->mapWithKeys(fn ($intent) => [
                            $intent->id => "{$intent->amount->formatted} - {$intent->driver}",
                        ]))
                    ->live(),
                TextInput::make('amount')
                    ->required()
                    ->label(__('lunarpanel::order.form.amount.label'))
                    ->suffix(fn ($record) => $record->currency->code)
                    ->default(fn ($record) => number_format($record->total->decimal, $record->currency->decimal_places, '.', ''))
                    ->live()
                    ->autocomplete(false)
                    ->minValue(
                        fn ($record) => 1 / $record->currency->factor
                    )
                    ->helperText(function (TextInput $component, $get, $state) {
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
                Toggle::make('confirm')
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
            ->action(function ($data, $record, Action $action) {
                $transaction = Transaction::findOrFail($data['transaction']);

                $response = $transaction->capture(bcmul($data['amount'], $record->currency->factor));

                if (! $response->success) {
                    $action->failureNotification(
                        fn () => Notification::make('capture_failure')->color('danger')->title($response->message)
                    );

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
    public function intents(): Collection
    {
        return $this->record->transactions()->whereType('intent')->whereSuccess(true)->get();
    }
}
