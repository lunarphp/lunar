<?php

namespace Lunar\Panel\Search\Sources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Panel\Search\SearchSource;
use Lunar\Panel\Sections\Sales\SalesSection;
use Lunar\Panel\Support\Position;

class OrderSearchSource extends SearchSource
{
    public function key(): string
    {
        return 'orders';
    }

    public function label(): string
    {
        return __('panel::search.source_orders');
    }

    public function icon(): string
    {
        return 'cart';
    }

    public function permission(): string
    {
        return SalesSection::ORDERS_PERMISSION;
    }

    public function position(): Position
    {
        return Position::priority(10);
    }

    /** @return Builder<Order> */
    public function query(): Builder
    {
        return Order::query()->with(['billingAddress', 'customer', 'currency']);
    }

    public function applyTerm(Builder $query, string $token): void
    {
        $like = "%{$token}%";

        $query->where('reference', 'like', $like)
            ->orWhere('customer_reference', 'like', $like)
            ->orWhereHas('billingAddress', fn (Builder $query) => $query
                ->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('company_name', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhere('postcode', 'like', $like))
            ->orWhereHas('customer', fn (Builder $query) => $query
                ->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like));
    }

    /** @param Order $model */
    public function row(Model $model): array
    {
        $billing = $model->billingAddress;

        $customerName = $billing
            ? (trim($billing->first_name.' '.$billing->last_name) ?: $billing->company_name)
            : ($model->customer ? trim($model->customer->first_name.' '.$model->customer->last_name) : null);

        $currency = $model->currency ?? Currency::getDefault();

        return [
            'id' => $model->id,
            'label' => $model->reference ?: '#'.$model->id,
            'hint' => implode(' · ', array_filter([
                $customerName ?: null,
                $currency ? (new PriceValue($model->total, $currency))->format() : null,
            ])) ?: null,
            'url' => route('panel.orders.show', $model),
        ];
    }
}
