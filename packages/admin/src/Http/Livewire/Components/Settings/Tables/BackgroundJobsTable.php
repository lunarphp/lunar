<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\ProgressColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\JobBatch;

class BackgroundJobsTable extends LunarTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            ProgressColumn::make('status')
                ->progress(function (JobBatch $batch) {
                    return match ($batch->getStatus()) {
                        'pending' => $batch->getProgress(),
                        default => 100,
                    };
                })
                ->label(function (JobBatch $batch) {
                    return match ($batch->getStatus()) {
                        'pending' => "{$batch->getProgress()}%",
                        'failed' => 'Failed',
                        'unhealthy' => 'Unhealthy',
                        'successful' => 'Completed',
                        'cancelled' => 'Cancelled',
                    };
                })
                ->color(function (JobBatch $batch) {
                    return match ($batch->getStatus()) {
                        'pending' => 'primary',
                        'failed' => 'danger',
                        'unhealthy' => 'warning',
                        'successful' => 'success',
                        'cancelled' => 'secondary',
                    };
                }),
            TextColumn::make('name'),
            TextColumn::make('id')->heading('Batch ID'),
            TextColumn::make('subject_id'),
            TextColumn::make('subject_type'),
            TextColumn::make('causer.email'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return JobBatch::with('causer')->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
}
