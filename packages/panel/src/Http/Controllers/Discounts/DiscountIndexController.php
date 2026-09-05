<?php

namespace Lunar\Panel\Http\Controllers\Discounts;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Lunar\Panel\Support\DiscountDataSchema;

class DiscountIndexController
{
    use ResolvesTableExtensions;

    /** @var string[] */
    protected array $sortable = ['name', 'priority', 'starts_at', 'ends_at', 'uses', 'created_at'];

    /** The four derived statuses, each backed by the scope of the same name. */
    protected const STATUSES = [
        Discount::ACTIVE,
        Discount::SCHEDULED,
        Discount::EXPIRED,
        Discount::PENDING,
    ];

    public function index(Request $request, DiscountDataSchema $dataSchema): Response
    {
        $columns = [
            ['key' => 'status', 'label' => __('panel::discounts.column_status'), 'width' => '110px'],
            ['key' => 'name', 'label' => __('panel::discounts.column_name'), 'width' => 'minmax(0,1.6fr)'],
            ['key' => 'type_label', 'label' => __('panel::discounts.column_type'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'coupon', 'label' => __('panel::discounts.column_coupon'), 'width' => 'minmax(0,0.9fr)'],
            ['key' => 'window', 'label' => __('panel::discounts.column_window'), 'width' => 'minmax(0,1fr)'],
            ['key' => 'usage', 'label' => __('panel::discounts.column_usage'), 'width' => '120px'],
            ['key' => 'priority', 'label' => __('panel::discounts.column_priority'), 'width' => '80px', 'align' => 'right'],
        ];

        $sort = $request->string('sort')->value();
        $sort = in_array($sort, $this->sortable, true) ? $sort : 'priority';

        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';

        $resolver = $this->resolveTable('discounts.index');

        $types = $this->types();

        $prefix = config('lunar.database.table_prefix');

        $defaultCurrency = Currency::getDefault();

        $discounts = Discount::query()
            ->when($request->filled('q'), function ($query) use ($request, $resolver) {
                $term = $request->string('q')->value();
                $like = "%{$term}%";

                $query->where(function ($query) use ($like, $term, $resolver) {
                    $query->where('name', 'like', $like)
                        ->orWhere('handle', 'like', $like)
                        ->orWhere('coupon', 'like', $like);

                    $resolver->applySearchQueries($query, $term);
                });
            })
            ->when(
                in_array($request->string('status')->value(), self::STATUSES, true),
                fn ($query) => $query->{$request->string('status')->value()}(),
            )
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->value()))
            // Both availability filters match on the pivot's `enabled` flag, not
            // on the row existing: HasChannels and HasCustomerGroups attach every
            // channel and group when a discount is created, so a filter on mere
            // attachment would match everything.
            ->when($request->filled('channel_id'), fn ($query) => $query->whereHas(
                'channels',
                fn ($query) => $query->where($query->getModel()->qualifyColumn('id'), $request->integer('channel_id'))
                    ->where($prefix.'channelables.enabled', true),
            ))
            ->when($request->filled('customer_group_id'), fn ($query) => $query->whereHas(
                'customerGroups',
                fn ($query) => $query->where($query->getModel()->qualifyColumn('id'), $request->integer('customer_group_id'))
                    ->where($prefix.'customer_group_discount.enabled', true),
            ))
            ->when($request->string('redemption')->value() === 'coupon', fn ($query) => $query->whereNotNull('coupon'))
            ->when($request->string('redemption')->value() === 'automatic', fn ($query) => $query->whereNull('coupon'))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (Discount $discount) use ($resolver, $types, $dataSchema, $defaultCurrency) {
                $status = $discount->status;

                $row = [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'handle' => $discount->handle,
                    'status' => $status,
                    'status_label' => __('panel::discounts.status_'.$status),
                    'type' => $discount->type,
                    // The registry is the source of truth for the label; a discount
                    // can outlive the package that registered its type, so fall back
                    // to the stored class rather than instantiating something gone.
                    'type_label' => $types[$discount->type] ?? $discount->type,
                    // The type's own one-liner ("15% off"), or null when it cannot
                    // summarise itself — the column then shows the label alone.
                    'effect' => $dataSchema->summary($discount->type, $discount->data ?? [], $defaultCurrency),
                    'coupon' => $discount->coupon,
                    'starts_at' => $discount->starts_at,
                    'ends_at' => $discount->ends_at,
                    'uses' => $discount->uses,
                    'max_uses' => $discount->max_uses,
                    'priority' => $discount->priority,
                    'edit_url' => route('panel.discounts.edit', $discount),
                    '_actions' => $resolver->resolveRowActionUrls($discount),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $discount->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('discounts/Index', [
            'discounts' => $discounts,
            ...$this->tableProps($resolver, $columns, $request),
            'types' => collect($types)->map(fn (string $label, string $class) => [
                'value' => $class,
                'label' => $label,
            ])->values(),
            'channels' => Channel::all(['id', 'name']),
            'customerGroups' => CustomerGroup::all(['id', 'name']),
            'totalCount' => Discount::count(),
            'kpis' => $this->kpis(),
            'filters' => $request->only(['q', 'status', 'type', 'channel_id', 'customer_group_id', 'redemption', 'sort', 'direction']),
            'urls' => [
                'index' => route('panel.discounts.index'),
                'create' => route('panel.discounts.create'),
            ],
        ]);
    }

    /**
     * Registered discount types as class => label. Driven by the registry, so a
     * type added by a third-party package appears in the filter and the rows
     * without this controller knowing about it.
     *
     * @return array<class-string, string>
     */
    protected function types(): array
    {
        return collect(Discounts::getTypes())
            ->mapWithKeys(fn ($type) => [$type::class => $type->getName()])
            ->all();
    }

    /**
     * KPI strip values, cached briefly — they are whole-table aggregates and do
     * not need to be second-accurate.
     *
     * Redemptions is a lifetime total rather than a 30-day window: `uses` is a
     * bare counter with no per-redemption timestamp, and the one timestamped
     * table (discount_user) only records signed-in redemptions, so a windowed
     * figure would silently omit guest checkouts.
     *
     * @return array{active: int, scheduled: int, endingSoon: int, redemptions: int}
     */
    protected function kpis(): array
    {
        return Cache::remember('lunar.panel.discounts.kpis', now()->addMinutes(5), fn (): array => [
            'active' => Discount::active()->count(),
            'scheduled' => Discount::scheduled()->count(),
            'endingSoon' => Discount::active()->whereNotNull('ends_at')->where('ends_at', '<=', now()->addDays(7))->count(),
            'redemptions' => (int) Discount::sum('uses'),
        ]);
    }
}
