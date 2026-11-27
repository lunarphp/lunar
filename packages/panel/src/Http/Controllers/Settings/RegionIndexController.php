<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Core\States\Channel\Active;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class RegionIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'name', 'label' => __('panel::regions.column_name'), 'width' => 'minmax(0, 1.3fr)'],
            ['key' => 'channel', 'label' => __('panel::regions.column_channel'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'currency', 'label' => __('panel::regions.column_currency'), 'width' => '110px'],
            ['key' => 'language', 'label' => __('panel::regions.column_language'), 'width' => '110px'],
            ['key' => 'countries_count', 'label' => __('panel::regions.column_countries'), 'width' => '100px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('regions.index');

        $regions = Region::query()
            ->with(['channel:id,name', 'currency:id,code', 'language:id,code'])
            ->withCount('countries')
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Region $region) use ($resolver): array {
                $row = [
                    'id' => $region->id,
                    'name' => $region->name,
                    'handle' => $region->handle,
                    'default' => $region->default,
                    'channel' => $region->channel?->name,
                    'currency' => $region->currency?->code,
                    'language' => $region->language?->code,
                    'countries_count' => (int) $region->getAttribute('countries_count'),
                    'urls' => [
                        'edit' => route('panel.settings.regions.edit', $region),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($region),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $region->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/regions/Index', [
            'regions' => $regions,
            ...$this->tableProps($resolver, $this->columns, $request),
            'channels' => Channel::query()->orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::query()->orderBy('code')->get(['id', 'code']),
            'languages' => Language::query()->orderBy('code')->get(['id', 'code']),
            'defaults' => $this->createDefaults(),
            'urls' => [
                'index' => route('panel.settings.regions.index'),
                'store' => route('panel.settings.regions.store'),
            ],
        ]);
    }

    /**
     * The channel and currency the create form starts on: the store's default
     * channel and currency, falling back to the first active channel and the
     * first enabled currency, then to the first record of each.
     *
     * @return array{channel_id: int|null, currency_id: int|null}
     */
    protected function createDefaults(): array
    {
        $channel = Channel::getDefault()
            ?? Channel::query()->whereState('status', Active::class)->orderBy('name')->first()
            ?? Channel::query()->orderBy('name')->first();

        $currency = Currency::getDefault()
            ?? Currency::query()->where('enabled', true)->orderBy('code')->first()
            ?? Currency::query()->orderBy('code')->first();

        return [
            'channel_id' => $channel?->id,
            'currency_id' => $currency?->id,
        ];
    }
}
