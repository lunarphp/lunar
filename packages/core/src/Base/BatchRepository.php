<?php

namespace Lunar\Base;

use Illuminate\Bus\DatabaseBatchRepository;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchRepository extends DatabaseBatchRepository
{

    /**
     * @inheritDoc
     */
    public function store(PendingBatch $batch)
    {
        return DB::transaction(function () use ($batch) {
            $batch = parent::store($batch);

            $causer = Auth::user();

            $this->connection
                ->table($this->table)
                ->where('id', $batch->id)
                ->update([
                    'causer_id' => $causer?->getKey(),
                    'causer_type' => $causer?->getMorphClass(),
                ]);

            return $batch;
        });
    }

    /**
     * @inheritDoc
     */
    public function toBatch($batch)
    {
        return parent::toBatch($batch);
    }
}
