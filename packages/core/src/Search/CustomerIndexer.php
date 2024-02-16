<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomerIndexer extends ScoutIndexer
{
    public function getSortableFields(): array
    {
        return [
            'created_at',
            'updated_at',
            'name',
            'company_name',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'name',
            'company_name',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'users',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $metaFields = (array) $model->meta;

        $data = array_merge([
            'id' => (string) $model->id,
            'name' => $model->fullName,
            'company_name' => $model->company_name,
            'vat_no' => $model->vat_no,
            'account_ref' => $model->account_ref,
            'created_at' => (integer) $model->created_at->timestamp,
        ], $this->mapSearchableAttributes($model));

        foreach ($metaFields as $key => $value) {
            $data[$key] = $value;
        }

        $data['user_emails'] = $model->users->pluck('email')->toArray();

        return $data;
    }
}
