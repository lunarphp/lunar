<?php

namespace Lunar\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderIndexer extends ScoutIndexer
{
    public function getSortableFields(): array
    {
        return [
            'customer_id',
            'user_id',
            'channel_id',
            'created_at',
            'updated_at',
            'total',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            'customer_id',
            'user_id',
            'status',
            'placed_at',
            'channel_id',
            'tags',
            '__soft_deleted',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'channel',
            'transactions',
            'productLines',
            'addresses',
            'tags',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $data = [
            'id' => $model->id,
            'channel' => $model->channel->name,
            'reference' => $model->reference,
            'customer_reference' => $model->customer_reference,
            'status' => $model->status,
            'placed_at' => optional($model->placed_at)->timestamp,
            'created_at' => $model->created_at->timestamp,
            'sub_total' => $model->sub_total->value,
            'total' => $model->total->value,
            'currency_code' => $model->currency_code,
            'charges' => $model->transactions->map(function ($transaction) {
                return [
                    'reference' => $transaction->reference,
                ];
            }),
            'currency' => $model->currency_code,
            'lines' => $model->productLines->map(function ($line) {
                return [
                    'description' => $line->description,
                    'identifier' => $line->identifier,
                ];
            })->toArray(),
        ];

        foreach ($model->addresses as $address) {
            $fields = [
                'first_name',
                'last_name',
                'company_name',
                'line_one',
                'line_two',
                'line_three',
                'city',
                'state',
                'postcode',
                'contact_email',
                'contact_phone',
            ];

            foreach ($fields as $field) {
                $data["{$address->type}_{$field}"] = $address->getAttribute($field);
            }

            $data["{$address->type}_country"] = optional($address->country)->name;

            // Full name for searching
            $data["{$address->type}_fullname"] = $address->first_name . ' ' . $address->last_name;
        }

        $data['tags'] = $model->tags->pluck('value')->toArray();

        return $data;
    }
}
