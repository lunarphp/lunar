<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Awcodes\Shout\Components\Shout;
use Awcodes\Shout\Components\ShoutEntry;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\OrderResource;
use Lunar\Admin\Support\Actions\Orders\UpdateStatusAction;
use Lunar\Admin\Support\Actions\PdfDownload;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Admin\Support\Concerns\CallsHooks;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Infolists\Components\Livewire;
use Lunar\Admin\Support\Infolists\Components\Tags;
use Lunar\Admin\Support\Pages\BaseViewRecord;
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
    use CallsHooks;
    use CanDispatchActivityUpdated;
    use OrderResource\Concerns\DisplaysOrderAddresses;
    use OrderResource\Concerns\DisplaysOrderSummary;
    use OrderResource\Concerns\DisplaysOrderTimeline;
    use OrderResource\Concerns\DisplaysOrderTotals;
    use OrderResource\Concerns\DisplaysShippingInfo;
    use OrderResource\Concerns\DisplaysTransactions;

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

    public static function getDefaultCustomerEntry(): Infolists\Components\Entry
    {
        return Infolists\Components\TextEntry::make('customer')
            ->hidden(fn ($state) => blank($state?->id))
            ->formatStateUsing(fn ($state) => $state->fullName)
            ->weight(FontWeight::SemiBold)
            ->size(TextEntrySize::Large)
            ->hiddenLabel()
            ->suffixAction(fn ($state) => Action::make('view customer')
                ->color('gray')
                ->button()
                ->size(ActionSize::ExtraSmall)
                ->url(CustomerResource::getUrl('edit', ['record' => $state->id])));
    }

    public static function getCustomerEntry(): Infolists\Components\Component
    {
        return self::callStaticLunarHook('extendCustomerEntry', static::getDefaultCustomerEntry());
    }

    public static function getDefaultTagsSection(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make('tags')
            ->heading(__('lunarpanel::order.infolist.tags.label'))
            ->headerActions([
                fn ($record) => static::getEditTagsActions(),
            ])
            ->compact()
            ->schema([
                Tags::make(''),
            ]);
    }

    public static function getTagsSection(): Infolists\Components\Component
    {
        return self::callStaticLunarHook('extendTagsSection', static::getDefaultTagsSection());
    }

    public static function getDefaultAdditionalInfoSection(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make('additional_info')
            ->heading(__('lunarpanel::order.infolist.additional_info.label'))
            ->compact()
            ->statePath('meta')
            ->schema(fn ($state) => blank($state) ? [
                Infolists\Components\TextEntry::make('no_additional_info')
                    ->hiddenLabel()
                    ->getStateUsing(fn () => __('lunarpanel::order.infolist.no_additional_info.label')),
            ] : collect($state)
                ->map(function ($value, $key) {
                    if (is_array($value)) {
                        return Infolists\Components\KeyValueEntry::make('meta_'.$key)->state($value);
                    }

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
                })
                ->toArray());
    }

    public static function getAdditionalInfoSection(): Infolists\Components\Component
    {
        return self::callStaticLunarHook('extendAdditionalInfoSection', static::getDefaultAdditionalInfoSection());
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
                        ...static::getInfolistSchema(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Infolists\Components\Group::make()
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
            ->form(function () {
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
                        fn ($record) => 1 / $record->currency->factor
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
                    ->minValue(
                        fn ($record) => 1 / $record->currency->factor
                    )
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
