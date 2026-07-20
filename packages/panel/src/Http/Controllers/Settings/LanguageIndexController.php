<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Language;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class LanguageIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'code', 'label' => __('panel::languages.column_code'), 'width' => '110px'],
            ['key' => 'name', 'label' => __('panel::languages.column_name'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'flags', 'label' => '', 'width' => '120px'],
        ];

        $resolver = $this->resolveTable('languages.index');

        $languages = Language::query()
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('code')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Language $language) use ($resolver): array {
                $row = [
                    'id' => $language->id,
                    'code' => $language->code,
                    'name' => $language->name,
                    'default' => $language->default,
                    'urls' => [
                        'edit' => route('panel.settings.languages.edit', $language),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($language),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $language->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/languages/Index', [
            'languages' => $languages,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.languages.index'),
                'store' => route('panel.settings.languages.store'),
            ],
        ]);
    }
}
