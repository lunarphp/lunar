<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Tags\CreatesTag;
use Lunar\Core\Contracts\Actions\Tags\DeletesTag;
use Lunar\Core\Contracts\Actions\Tags\UpdatesTag;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Tag;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Lunar\Panel\Http\Requests\Settings\TagRequest;

class TagController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'value', 'label' => __('panel::tags.column_value'), 'width' => 'minmax(0, 1.4fr)'],
            ['key' => 'usage_count', 'label' => __('panel::tags.column_usage'), 'width' => '110px', 'align' => 'right'],
        ];

        $resolver = $this->resolveTable('tags.index');

        $usage = DB::table(config('lunar.database.table_prefix').'taggables')
            ->selectRaw('tag_id, COUNT(*) AS aggregate')
            ->groupBy('tag_id')
            ->pluck('aggregate', 'tag_id');

        $tags = Tag::query()
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->orderBy('value')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Tag $tag) use ($resolver, $usage): array {
                $row = [
                    'id' => $tag->id,
                    'value' => $tag->value,
                    'usage_count' => (int) ($usage[$tag->id] ?? 0),
                    'urls' => [
                        'update' => route('panel.settings.tags.update', $tag),
                        'destroy' => route('panel.settings.tags.destroy', $tag),
                    ],
                    '_actions' => $resolver->resolveRowActionUrls($tag),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $tag->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/tags/Index', [
            'tags' => $tags,
            ...$this->tableProps($resolver, $this->columns, $request),
            'urls' => [
                'index' => route('panel.settings.tags.index'),
                'store' => route('panel.settings.tags.store'),
            ],
        ]);
    }

    public function store(TagRequest $request, CreatesTag $createsTag): RedirectResponse
    {
        $createsTag->execute($request->tagAttributes());

        return back()->with('success', __('panel::tags.flash_created'));
    }

    public function update(TagRequest $request, Tag $tag, UpdatesTag $updatesTag): RedirectResponse
    {
        $updatesTag->execute($tag, $request->tagAttributes());

        return back()->with('success', __('panel::tags.flash_updated'));
    }

    public function destroy(Tag $tag, DeletesTag $deletesTag): RedirectResponse
    {
        $deletesTag->execute($tag);

        return back()->with('success', __('panel::tags.flash_deleted'));
    }
}
