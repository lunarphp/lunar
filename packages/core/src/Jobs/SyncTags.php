<?php

namespace GetCandy\Jobs;

use GetCandy\Models\Tag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncTags implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The product instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected Model $model;

    /**
     * The option values to use to generate variants.
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $tags;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Illuminate\Support\Collection      $tags
     *
     * @return void
     */
    public function __construct(Model $model, Collection $tags)
    {
        $this->model = $model;
        $this->tags = $tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $tagIds = [];
            // Make sure the tags are uppercase
            $this->tags->map(fn ($tag) => Str::upper($tag))
            ->each(function ($tag) use (&$tagIds) {
                $model = Tag::firstOrCreate([
                    'value' => $tag,
                ]);
                $tagIds[] = $model->id;
            });
            $this->model->tags()->sync($tagIds);
        });
    }
}
