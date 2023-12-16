<?php

namespace Lunar\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as AttributeCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Base\BatchRepository;
use Lunar\Database\Factories\JobBatchFactory;

/**
 *
 * @property string $name
 * @property int $total_jobs
 * @property int $pending_jobs
 * @property int $failed_jobs
 * @property DateTimeInterface $cancelled_at
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $finished_at
 * @property-read string $subject_type
 * @property-read int $subject_id
 * @property-read string $causer_type
 * @property-read int $causer_id
 * @property-read string $failed_job_ids
 * @property-read array $tags
 * @property-read string $status
 *
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $causer
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Lunar\Models\JobBatch causedBy(\Illuminate\Database\Eloquent\Model $causer)
 * @method static \Illuminate\Database\Eloquent\Builder|\Lunar\Models\JobBatch forSubject(\Illuminate\Database\Eloquent\Model $subject)
 */
class JobBatch extends BaseModel
{
    use HasFactory;

    /**
     * Indicates batch has pending jobs
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Indicates batch has finished and all jobs have failed
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Indicates batch has finished and some jobs have failed
     */
    public const STATUS_UNHEALTHY = 'unhealthy';

    /**
     * Indicates batch has finished successfully
     */
    public const STATUS_SUCCESSFUL = 'successful';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_batches';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'failed_jobs' => 'integer',
        'created_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return JobBatchFactory
     */
    protected static function newFactory(): JobBatchFactory
    {
        return JobBatchFactory::new();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCausedBy(Builder $query, BaseModel $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, BaseModel $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /**
     * Get the total number of jobs that have been processed by the batch thus far.
     *
     * @return int
     */
    public function getProcessedJobs(): int
    {
        return $this->total_jobs - $this->pending_jobs;
    }

    /**
     * Get the percentage of jobs that have been processed (between 0-100).
     *
     * @return int
     */
    public function getProgress(): int
    {
        return $this->total_jobs > 0 ? round(($this->getProcessedJobs() / $this->total_jobs) * 100) : 0;
    }

    /**
     * Determine if the batch has pending jobs
     *
     * @return bool
     */
    public function hasPendingJobs(): bool
    {
        return $this->pending_jobs > 0;
    }

    /**
     * Determine if the batch has finished executing.
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return !is_null($this->finished_at);
    }

    /**
     * Determine if the batch has job failures.
     *
     * @return bool
     */
    public function hasFailures(): bool
    {
        return $this->failed_jobs > 0;
    }

    /**
     * Determine if all jobs failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->failed_jobs === $this->total_jobs;
    }

    /**
     * Determine if the batch has been canceled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    /**
     * Status accessor
     *
     * @return AttributeCast
     */
    protected function status(): AttributeCast
    {
        return (new AttributeCast(
            get: fn() => match (true) {
                $this->hasPendingJobs() => self::STATUS_PENDING,
                $this->isFinished() && $this->isFailed() => self::STATUS_FAILED,
                $this->isFinished() && $this->hasFailures() => self::STATUS_UNHEALTHY,
                default => self::STATUS_SUCCESSFUL,
            },
        ));
    }

    /**
     * Tags accessor
     *
     * @return AttributeCast
     */
    protected function tags(): AttributeCast
    {
        return (new AttributeCast(
            get: fn($value) => $this->toBatchDTO()->options['tags'] ?? [],
        ));
    }

    /**
     * Converts instance of JobBatch model to laravel Illuminate\Bus\Batch instance
     *
     * @return \Illuminate\Bus\Batch
     */
    public function toBatchDTO(): \Illuminate\Bus\Batch
    {
        return app(BatchRepository::class, ['table' => $this->table])->toBatch($this);
    }
}
