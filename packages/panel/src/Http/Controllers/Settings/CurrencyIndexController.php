<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Currency;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class CurrencyIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'code', 'label' => __('panel::currencies.column_code'), 'width' => '110px'],
            ['key' => 'name', 'label' => __('panel::currencies.column_name'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'exchange_rate', 'label' => __('panel::currencies.column_exchange_rate'), 'width' => '140px', 'align' => 'right'],
            ['key' => 'flags', 'label' => '', 'width' => '170px'],
        ];

        $resolver = $this->resolveTable('currencies.index');

        $currencies = Currency::query()
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Currency $currency) use ($resolver): array {
                $row = [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'name' => $currency->name,
                    'exchange_rate' => (float) $currency->exchange_rate,
                    'decimal_places' => $currency->decimal_places,
                    'enabled' => $currency->enabled,
                    'default' => $currency->default,
                    'urls' => [
                        'edit' => route('panel.settings.currencies.edit', $currency),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($currency),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $currency->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/currencies/Index', [
            'currencies' => $currencies,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.currencies.index'),
                'store' => route('panel.settings.currencies.store'),
            ],
        ]);
    }
}
