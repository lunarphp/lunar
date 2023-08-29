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
            'first_name',
            'last_name',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'company_name',
            'first_name',
            'last_name',
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
            'id' => $model->id,
            'name' => $model->fullName,
            'company_name' => $model->company_name,
            'vat_no' => $model->vat_no,
            'account_ref' => $model->account_ref,
        ], $this->mapSearchableAttributes($model));

        foreach ($metaFields as $key => $value) {
            $data[$key] = $value;
        }

        $data['user_emails'] = $model->users->pluck('email')->toArray();

        return $data;
    }
}
