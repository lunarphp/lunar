## Extending Order Management

Although orders have access to the same customisation as [Pages](/admin/extending/pages) there are some additional methods available and an additional class to allow order lines to be customised.

To register your extension:

```php
LunarPanel::extensions([
    \Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder::class => MyManageOrderExtension::class,
]);
```

You then have access to these methods in your class to override area's of the order view screen.

- `extendInfolistSchema(): array`

- `extendInfolistAsideSchema(): array`
    - `extendCustomerEntry(): Infolists\Components\Component`
    - `extendTagsSection(): Infolists\Components\Component`
    - `extendAdditionalInfoSection(): Infolists\Components\Component`
    - `extendShippingAddressInfolist(): Infolists\Components\Component`
    - `extendBillingAddressInfolist(): Infolists\Components\Component`
    -  `extendAddressEditSchema(): array`

- `exendOrderSummaryInfolist(): Infolists\Components\Section`
- `extendOrderSummarySchema(): array`
    - `extendOrderSummaryNewCustomerEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryStatusEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryReferenceEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryCustomerReferenceEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryChannelEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryCreatedAtEntry(): Infolists\Components\Entry`
    - `extendOrderSummaryPlacedAtEntry(): Infolists\Components\Entry`
- `extendTimelineInfolist(): Infolists\Components\Component`
- `extendOrderTotalsAsideSchema(): array`
    - `extendDeliveryInstructionsEntry(): Infolists\Components\TextEntry`
    - `extendOrderNotesEntry(): Infolists\Components\TextEntry`
- `extendOrderTotalsInfolist(): Infolists\Components\Section`
- `extendOrderTotalsSchema(): array`
    - `extendSubTotalEntry(): Infolists\Components\TextEntry`
    - `extendDiscountTotalEntry(): Infolists\Components\TextEntry`
    - `extendShippingBreakdownGroup(): Infolists\Components\Group`
    - `extendTaxBreakdownGroup(): Infolists\Components\Group`
    - `extendTotalEntry(): Infolists\Components\TextEntry`
    - `extendPaidEntry(): Infolists\Components\TextEntry`
    - `extendRefundEntry(): Infolists\Components\TextEntry`

- `extendShippingInfolist(): Infolists\Components\Section`
- `extendTransactionsInfolist(): Infolists\Components\Component`
- `extendTransactionsRepeatableEntry(): Infolists\Components\RepeatableEntry`

## Extending `OrderItemsTable`

```php
\Lunar\Facades\LunarPanel::extensions([
    \Lunar\Admin\Filament\Resources\OrderResource\Pages\Components\OrderItemsTable::class => OrderItemsTableExtension::class
]);
```
### Available Methods

- `extendOrderLinesTableColumns(): array`
- `extendTable(): Table`
