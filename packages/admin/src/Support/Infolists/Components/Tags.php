<?php

namespace Lunar\Admin\Support\Infolists\Components;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Filament\Infolists\Components\TextEntry;

class Tags extends TextEntry
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->badge();
    }

    public function getState(): array
    {
        $state = parent::getState();

        $record = $this->getRecord();

        if (! $record) {
            return [];
        }

        if (! method_exists($record, 'tags')) {
            return [];
        }

        return $record
                ->tags
                ->pluck('value')
                ->map(function (string $value) {
                    return Str::upper($value);
                })->all();
    }
}
