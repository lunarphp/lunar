<?php

namespace GetCandy\Jobs\Collections;

use GetCandy\Models\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RebuildCollectionTree implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $tries = 1;

    /**
     * The new collection tree structure.
     *
     * @var array
     */
    protected $newTree = [];

    /**
     * The current collection tree.
     *
     * @var array
     */
    protected $currentTree = [];

    /**
     * The collection parent for sub tree rebuilding.
     *
     * @var null|int|string
     */
    protected $parent;

    /**
     * Create a new job instance.
     *
     * @param array           $newTree
     * @param array           $currentTree
     * @param string|int|null $parent
     */
    public function __construct(array $newTree, array $currentTree, Collection $parent = null)
    {
        $this->newTree = $newTree;
        $this->currentTree = $currentTree;
        $this->parent = $parent;
    }

    public function handle()
    {
        DB::transaction(function () {
            if ($this->parent) {
                Collection::rebuildSubtree($this->parent, collect($this->newTree)->map(fn ($value) => [
                    'id' => $value['id'],
                ])->toArray());

                return;
            }

            foreach ($this->newTree as $row) {
                $index = collect($this->currentTree)->search(function ($node) use ($row) {
                    return $node['id'] == $row['id'];
                });

                $out = array_splice($this->currentTree, $index, 1);
                array_splice($this->currentTree, $row['order'] - 1, 0, $out);
            }

            Collection::rebuildTree($this->currentTree);
        });
    }
}
