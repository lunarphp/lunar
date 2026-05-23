<?php

namespace Lunar\Filament\Forms\Components\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Livewire\Component as LivewireComponent;

/**
 * Opt-in dedup against the relation managed by the surrounding Livewire
 * component (a Filament RelationManager). Selectors using this trait
 * call {@see filterAttachedRecords()} from their own search closure to
 * apply the filter after results come back. {@see SearchesLunarRecords}
 * does this automatically; bespoke selectors (e.g. {@see CustomerSelect})
 * must apply it explicitly.
 */
trait ExcludesAttachedRecords
{
    protected bool|Closure $excludeAttached = false;

    public function excludeAttached(bool|Closure $condition = true): static
    {
        $this->excludeAttached = $condition;

        if (method_exists($this, 'filterOptionsUsing')) {
            $this->filterOptionsUsing(fn (Model $record): bool => $this->isAttached($record));
        }

        return $this;
    }

    public function shouldExcludeAttached(): bool
    {
        return (bool) $this->evaluate($this->excludeAttached);
    }

    public function isAttached(Model $record): bool
    {
        if (! $this->shouldExcludeAttached()) {
            return false;
        }

        return $this->resolveAttachedKeys()->contains($record->getKey());
    }

    /**
     * @param  Collection<int, Model>  $records
     * @return Collection<int, Model>
     */
    public function filterAttachedRecords(Collection $records): Collection
    {
        if (! $this->shouldExcludeAttached()) {
            return $records;
        }

        $attached = $this->resolveAttachedKeys();

        return $records->reject(fn (Model $record): bool => $attached->contains($record->getKey()));
    }

    /**
     * @return Collection<int, int|string>
     */
    protected function resolveAttachedKeys(): Collection
    {
        $livewire = $this->getLivewire();

        if (! $livewire instanceof LivewireComponent) {
            return collect();
        }

        if (! method_exists($livewire, 'getRelationship')) {
            return collect();
        }

        $relationship = $livewire->getRelationship();

        if (! $relationship instanceof Relation) {
            return collect();
        }

        return $relationship->get()->map->getKey();
    }
}
