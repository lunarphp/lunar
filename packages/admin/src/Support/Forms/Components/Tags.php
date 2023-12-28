<?php

namespace Lunar\Admin\Support\Forms\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Forms\Components\TagsInput;
use Lunar\Admin\Support\ActivityLog\Concerns\CanDispatchActivityUpdated;
use Lunar\Models\Tag;
use Lunar\Facades\DB;

class Tags extends TagsInput
{
    use CanDispatchActivityUpdated;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadStateFromRelationshipsUsing(static function (Tags $component, ?Model $record): void {
            if (!method_exists($record, 'tags')) {
                return;
            }

            $state = $record->tags->pluck('value')->map(function (string $value) {
                return Str::upper($value);
            })->all();

            $component->state($state);
        });

        $this->saveRelationshipsUsing(static function (Tags $component, ?Model $record, array $state) {
            if (!(method_exists($record, 'tags'))) {
                return;
            }

            $component->syncTags($record, $state);
        });

        $this->dehydrated(false);
    }

    public function syncTags(?Model $record, array $state): void
    {
        // value fields is utf8mb4_unicode_ci prevent no case sensitive
        $state = collect($state)->map(function (string $value) {
            return Str::upper($value);
        })->toArray();

        DB::transaction(function () use ($record, $state) {

            $databaseTags = Tag::whereIn('value', $state)->get();

            $newTags = collect($state)->filter(function ($value) use ($databaseTags) {
                return ! $databaseTags->pluck('value')->contains($value);
            });

            $currentTags = $record->tags()->pluck('value');

            $addedTags = collect($state)->diff($currentTags);
            $removedTags = $currentTags->diff($state);

            $record->tags()->sync($databaseTags);

            foreach ($newTags as $tag) {
                $record->tags()->create([
                    'value' => $tag,
                ]);
            }

            if ($addedTags->count() || $removedTags->count()) {
                activity()
                    ->causedBy(Filament::auth()->user())
                    ->performedOn($record)
                    ->event('tags-update')
                    ->withProperties([
                        'added' => $addedTags->all(),
                        'removed' => $removedTags->all(),
                    ])->log('tags-update');
            }
        });
    }
}
