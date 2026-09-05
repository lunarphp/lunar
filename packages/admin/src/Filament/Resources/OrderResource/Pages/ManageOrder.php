<?php

namespace Lunar\Admin\Filament\Resources\OrderResource\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
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
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysFulfilments;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderAddresses;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderSummary;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderTimeline;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysOrderTotals;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysShippingInfo;
use Lunar\Admin\Filament\Resources\OrderResource\Concerns\DisplaysTransactions;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable;
use Lunar\Admin\Support\Actions\Orders\DownloadOrderPdfAction;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Admin\Support\Pages\BaseViewRecord;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Tag;
use Lunar\Filament\Actions\Orders\CancelOrderAction;
use Lunar\Filament\Actions\Orders\CaptureOrderAction;
use Lunar\Filament\Actions\Orders\CloseOrderAction;
use Lunar\Filament\Actions\Orders\NotifyCustomerAction;
use Lunar\Filament\Actions\Orders\RefundOrderAction;
use Lunar\Filament\Actions\Orders\ReopenOrderAction;
use Lunar\Filament\Forms\Components\Tags as TagsComponent;
use Lunar\Filament\Infolists\Components\Tags;
use Lunar\Filament\Support\Concerns\CallsHooks;

/**
 * @property Order $record
 * @property Collection $transactions
 * @property string $paymentStatus
 * @property bool $requiresCapture
 * @property int $captureTotal
 * @property int $refundTotal
 * @property int $intentTotal
 */
class ManageOrder extends BaseViewRecord
{
    use CallsHooks;
    use CanDispatchActivityUpdated;
    use DisplaysFulfilments;
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
        return Livewire::make(
            OrderItemsTable::class,
            fn ($record) => ['record' => $record],
        )->key('lunar_livewire_order_lines');
    }

    /**
     * Non-shipping lines not yet allocated to a fulfilment (shipping has its
     * own section; allocated lines live in their fulfilment card). Shown
     * regardless of type, so non-fulfillable service / custom-purchasable lines
     * still render. Hidden when every line is either shipping or allocated to a
     * fulfilment.
     */
    public static function getOtherItemsSection(): Component
    {
        return Section::make('other_items')
            ->heading(__('lunarpanel::order.other_items.heading'))
            ->compact()
            ->visible(function ($record) {
                return $record->lines()
                    ->where('type', '!=', 'shipping')
                    ->withoutFulfilment()
                    ->exists();
            })
            ->schema([
                static::getOrderLinesTable(),
            ]);
    }

    public static function getInfolistSchema(): array
    {
        return self::callStaticLunarHook('extendInfolistSchema', [
            static::getShippingInfolist(),
            static::getFulfilmentsInfolist(),
            static::getOtherItemsSection(),
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
                        Group::make()->key('shouts')
                            ->visible(fn () => $this->requiresCapture || in_array($this->paymentStatus, ['partial-refund', 'refunded']))
                            ->schema([
                                Callout::make()
                                    ->status('danger')
                                    ->heading(__('lunarpanel::order.infolist.alert.requires_capture'))
                                    ->visible(fn () => $this->requiresCapture),
                                Callout::make()
                                    ->key('partially_refunded_notice')
                                    ->icon(fn () => match ($this->paymentStatus) {
                                        'refunded' => FilamentIcon::resolve('lunar::exclamation-circle'),
                                        default => null
                                    })
                                    ->status(fn () => match ($this->paymentStatus) {
                                        'partial-refund' => 'info',
                                        'refunded' => 'danger',
                                        default => null
                                    })->heading(fn () => match ($this->paymentStatus) {
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
        })->sum('amount');
    }

    /**
     * Return the total amount refunded.
     */
    #[Computed()]
    public function refundTotal(): int
    {
        return $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'refund' && $transaction->success;
        })->sum('amount');
    }

    /**
     * Return the total amount intent.
     */
    #[Computed]
    public function intentTotal(): int
    {
        return $this->transactions->filter(function ($transaction) {
            return $transaction->type == 'intent' && $transaction->success;
        })->sum('amount');
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
        $bumpActivity = fn () => $this->dispatchActivityUpdated();

        return [
            CaptureOrderAction::make(),
            RefundOrderAction::make(),
            CloseOrderAction::make()->after($bumpActivity),
            ReopenOrderAction::make()->after($bumpActivity),
            CancelOrderAction::make()->after($bumpActivity),
            NotifyCustomerAction::make()->after($bumpActivity),
            DownloadOrderPdfAction::make(),
        ];
    }
}
