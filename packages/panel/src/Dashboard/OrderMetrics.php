<?php

namespace Lunar\Panel\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use NumberFormatter;

/**
 * Shared helpers for order-derived widget data: placed, non-cancelled orders
 * in a window, valued in the default currency via the exchange rate captured
 * at placement — the same basis as the customer order-value chart, so every
 * money figure on the dashboard agrees.
 *
 * Figures are aggregated in SQL (COUNT/SUM grouped in the database) rather than
 * by hydrating orders and summing in PHP: a busy store's window can hold tens
 * of thousands of orders, and the dashboard only needs the totals.
 */
class OrderMetrics
{
    protected ?Currency $defaultCurrency = null;

    protected bool $currencyResolved = false;

    /** @return Builder<Order> */
    public function placedOrders(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Order::query()
            ->whereNotNull('placed_at')
            ->whereNull('cancelled_at')
            ->where('placed_at', '>=', $start)
            ->where('placed_at', '<', $end);
    }

    /**
     * Order count and default-currency revenue (minor units) for a window,
     * split into new-customer and returning-customer halves — one grouped query.
     *
     * @return object{orders: int, revenue: int, newOrders: int, newRevenue: int, repeatOrders: int, repeatRevenue: int}
     */
    public function totals(CarbonImmutable $start, CarbonImmutable $end): object
    {
        $rows = $this->placedOrders($start, $end)
            ->toBase()
            ->selectRaw('new_customer, COUNT(*) as orders, COALESCE(SUM('.$this->valueInDefaultCurrency('total').'), 0) as revenue')
            ->groupBy('new_customer')
            ->get();

        $newOrders = $repeatOrders = 0;
        $newRevenue = $repeatRevenue = 0.0;

        foreach ($rows as $row) {
            if (filter_var($row->new_customer, FILTER_VALIDATE_BOOLEAN)) {
                $newOrders = (int) $row->orders;
                $newRevenue = (float) $row->revenue;
            } else {
                $repeatOrders = (int) $row->orders;
                $repeatRevenue = (float) $row->revenue;
            }
        }

        return (object) [
            'orders' => $newOrders + $repeatOrders,
            'revenue' => (int) round($newRevenue + $repeatRevenue),
            'newOrders' => $newOrders,
            'newRevenue' => (int) round($newRevenue),
            'repeatOrders' => $repeatOrders,
            'repeatRevenue' => (int) round($repeatRevenue),
        ];
    }

    /**
     * Per-bucket revenue (minor units), order count and new-customer order
     * count across the window, grouped in SQL and aligned to $range->buckets().
     *
     * @return array{revenue: array<int, int>, orders: array<int, int>, newOrders: array<int, int>}
     */
    public function series(DashboardRange $range): array
    {
        $buckets = $range->buckets();
        $hourly = $range === DashboardRange::Today;
        $bucketExpression = $this->bucketExpression($hourly);

        $rows = $this->placedOrders($range->start(), $range->end())
            ->toBase()
            ->selectRaw($bucketExpression.' as bucket, new_customer, COUNT(*) as orders, COALESCE(SUM('.$this->valueInDefaultCurrency('total').'), 0) as revenue')
            ->groupByRaw($bucketExpression.', new_customer')
            ->get();

        $revenue = $orders = $newOrders = [];

        foreach ($rows as $row) {
            $key = $hourly ? (string) (int) $row->bucket : (string) $row->bucket;
            $revenue[$key] = ($revenue[$key] ?? 0.0) + (float) $row->revenue;
            $orders[$key] = ($orders[$key] ?? 0) + (int) $row->orders;

            if (filter_var($row->new_customer, FILTER_VALIDATE_BOOLEAN)) {
                $newOrders[$key] = ($newOrders[$key] ?? 0) + (int) $row->orders;
            }
        }

        $revenueSeries = $orderSeries = $newOrderSeries = [];

        foreach ($buckets as $bucket) {
            $key = $this->bucketKey($bucket['start'], $hourly);
            $revenueSeries[] = (int) round($revenue[$key] ?? 0.0);
            $orderSeries[] = (int) ($orders[$key] ?? 0);
            $newOrderSeries[] = (int) ($newOrders[$key] ?? 0);
        }

        return ['revenue' => $revenueSeries, 'orders' => $orderSeries, 'newOrders' => $newOrderSeries];
    }

    /**
     * Default-currency revenue (minor units) for the window, grouped by an
     * order column (e.g. `channel_id`, `customer_id`). Rows with a null value
     * pool under the empty-string key.
     *
     * @return Collection<array-key, int>
     */
    public function revenueByColumn(CarbonImmutable $start, CarbonImmutable $end, string $column): Collection
    {
        return $this->placedOrders($start, $end)
            ->toBase()
            ->selectRaw($column.' as group_value, COALESCE(SUM('.$this->valueInDefaultCurrency('total').'), 0) as revenue')
            ->groupBy($column)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->group_value ?? '' => (int) round((float) $row->revenue)]);
    }

    /**
     * SQL expression valuing a minor-unit amount column in the default
     * currency, dividing by the order's captured exchange rate (treating a
     * zero/null rate as 1, matching `$amount / ($rate ?: 1)`).
     */
    public function valueInDefaultCurrency(string $amount, string $rate = 'exchange_rate'): string
    {
        return $amount.' / COALESCE(NULLIF('.$rate.', 0), 1)';
    }

    public function currency(): ?Currency
    {
        if (! $this->currencyResolved) {
            $this->defaultCurrency = Currency::getDefault();
            $this->currencyResolved = true;
        }

        return $this->defaultCurrency;
    }

    /** Formatted default-currency money, e.g. "£1,234.56". */
    public function format(int $minor): string
    {
        if ($currency = $this->currency()) {
            return (string) (new PriceValue($minor, $currency))->format();
        }

        return (string) ($minor / 100);
    }

    /**
     * Abbreviated default-currency money for tight summary readouts (a donut
     * centre, a KPI hero number), e.g. "£1.4M". Amounts below the threshold
     * fall through to the full format() so smaller stores keep exact figures;
     * the exact value stays available in a tooltip and, on the donuts, in the
     * per-segment legend. ICU has no compact-currency mode, so the k/M/B
     * suffix is appended after a fraction-capped currency format — correct for
     * the symbol, separators and sign; the suffix itself is a lang key.
     */
    public function formatCompact(int $minor): string
    {
        $currency = $this->currency();
        $major = $this->major($minor);

        if (! $currency || abs($major) < 10_000) {
            return $this->format($minor);
        }

        [$divisor, $suffix] = match (true) {
            abs($major) >= 1_000_000_000 => [1_000_000_000, __('panel::dashboard.compact_billion')],
            abs($major) >= 1_000_000 => [1_000_000, __('panel::dashboard.compact_million')],
            default => [1_000, __('panel::dashboard.compact_thousand')],
        };

        $formatter = new NumberFormatter(App::currentLocale(), NumberFormatter::CURRENCY);
        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currency->code);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 1);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);

        return $formatter->format($major / $divisor).$suffix;
    }

    /** Major-unit numeric value, for chart geometry. */
    public function major(int $minor): float
    {
        if ($currency = $this->currency()) {
            return round($minor / $currency->factor, $currency->decimal_places);
        }

        return $minor / 100;
    }

    /** The database bucket-grouping expression: hour-of-day, else calendar day. */
    protected function bucketExpression(bool $hourly): string
    {
        $driver = DB::connection()->getDriverName();
        $column = 'placed_at';

        if ($hourly) {
            return match ($driver) {
                'sqlite' => "CAST(strftime('%H', {$column}) AS INTEGER)",
                'pgsql' => "EXTRACT(HOUR FROM {$column})",
                default => "HOUR({$column})",
            };
        }

        return match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM-DD')",
            default => "DATE({$column})",
        };
    }

    /** The map key for a bucket start, matching bucketExpression()'s output. */
    protected function bucketKey(CarbonImmutable $start, bool $hourly): string
    {
        return $hourly ? (string) (int) $start->format('G') : $start->format('Y-m-d');
    }
}
