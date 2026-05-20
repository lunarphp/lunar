<?php

declare(strict_types=1);

namespace Lunar\Upgrade\Support;

/**
 * @phpstan-type ReportRow array{step: string, status: string, summary: string, manualActions: array<int, string>}
 */
class StepReport
{
    public const STATUS_OK = 'ok';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_DRY_RUN = 'dry-run';

    public const STATUS_FAILED = 'failed';

    /** @var array<int, ReportRow> */
    protected array $rows = [];

    /**
     * @param  array<int, string>  $manualActions
     */
    public function record(string $step, string $status, string $summary, array $manualActions = []): void
    {
        $this->rows[] = [
            'step' => $step,
            'status' => $status,
            'summary' => $summary,
            'manualActions' => $manualActions,
        ];
    }

    /** @return array<int, ReportRow> */
    public function rows(): array
    {
        return $this->rows;
    }

    /** @return array<int, string> */
    public function manualActions(): array
    {
        return array_merge(...array_column($this->rows, 'manualActions')) ?: [];
    }
}
