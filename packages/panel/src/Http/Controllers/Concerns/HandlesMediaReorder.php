<?php

namespace Lunar\Panel\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Media\ReordersMedia;
use Spatie\MediaLibrary\HasMedia;

trait HandlesMediaReorder
{
    protected function reorderMedia(HasMedia $model, Request $request, ReordersMedia $reordersMedia): RedirectResponse
    {
        $validated = $request->validate([
            'collection' => ['nullable', 'string', Rule::in($model->getRegisteredMediaCollections()->pluck('name'))],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        try {
            $reordersMedia->execute(
                $model,
                $validated['ids'],
                $validated['collection'] ?? config('lunar.media.collection'),
            );
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'ids' => __('panel::media.reorder_mismatch'),
            ]);
        }

        return back();
    }
}
