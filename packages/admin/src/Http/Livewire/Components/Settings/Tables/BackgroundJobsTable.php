<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\ProgressColumn;
use Lunar\LivewireTables\Components\Columns\TagsColumn;
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
        $this->defineColumns();
    }

    /**
     * Define table columns
     * @return void
     */
    private function defineColumns()
    {
        $this->tableBuilder->baseColumns([
            ProgressColumn::make('status')
                ->progress(function (JobBatch $batch) {
                    return match ($batch->status) {
                        JobBatch::STATUS_PENDING => $batch->getProgress(),
                        default => 100,
                    };
                })
                ->label(function (JobBatch $batch) {
                    return match ($batch->status) {
                        JobBatch::STATUS_PENDING => "{$batch->getProgress()}%",
                        JobBatch::STATUS_FAILED => 'Failed',
                        JobBatch::STATUS_UNHEALTHY => 'Unhealthy',
                        JobBatch::STATUS_SUCCESSFUL => 'Completed',
                        JobBatch::STATUS_CANCELLED => 'Cancelled',
                    };
                })
                ->color(function (JobBatch $batch) {
                    return match ($batch->status) {
                        JobBatch::STATUS_PENDING => 'primary',
                        JobBatch::STATUS_FAILED => 'danger',
                        JobBatch::STATUS_UNHEALTHY => 'warning',
                        JobBatch::STATUS_SUCCESSFUL => 'success',
                        JobBatch::STATUS_CANCELLED => 'secondary',
                    };
                }),

            TagsColumn::make('tags')
                ->heading('Tags')
                ->value(fn(JobBatch $batch) => $batch->options['tags'] ?? []),

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
