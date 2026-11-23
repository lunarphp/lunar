<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Lunar\Core\Contracts\Actions\Media\AddsMedia;
use Lunar\Panel\Contracts\DraftManager;
use Lunar\Panel\Http\Requests\Media\MediaStoreRequest;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\Media\MediaGroups;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Uploads land on the staff member's draft for the record, not on the
 * record: the response carries each staged item's id and preview, which the
 * page appends to the collection's draft list. Only Save moves them onto
 * the record.
 */
trait StagesMedia
{
    protected function stage(MediaStoreRequest $request, HasMedia&Model $record): JsonResponse
    {
        $staff = app(PanelManager::class)->user();

        abort_unless($staff !== null, 403);

        $draft = app(DraftManager::class)->stagingDraft($record, $staff);
        $collection = $request->resolvedCollection();
        $isImage = MediaGroups::isImageCollection($record, $collection);

        $items = collect($request->validated()['files'])
            ->map(fn ($file): Media => app(AddsMedia::class)->execute($draft, $file, $collection))
            ->map(fn (Media $media): array => MediaGroups::item($media, $isImage))
            ->values();

        return response()->json(['items' => $items]);
    }
}
