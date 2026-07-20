<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\TaxClass;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class TaxClassIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::tax_classes.column_name'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'variants_count', 'label' => __('panel::tax_classes.column_variants'), 'width' => '110px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('tax-classes.index');

        $taxClasses = TaxClass::query()
            ->withCount('productVariants')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (TaxClass $taxClass) use ($resolver): array {
                $row = [
                    'id' => $taxClass->id,
                    'name' => $taxClass->name,
                    'default' => $taxClass->default,
                    'variants_count' => (int) $taxClass->getAttribute('product_variants_count'),
                    'urls' => [
                        'edit' => route('panel.settings.tax-classes.edit', $taxClass),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($taxClass),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $taxClass->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/tax-classes/Index', [
            'taxClasses' => $taxClasses,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.tax-classes.index'),
                'store' => route('panel.settings.tax-classes.store'),
            ],
        ]);
    }
}
