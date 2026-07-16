<?php

namespace Lunar\Core\Search;

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
            'closed_at',
            'total',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            'customer_id',
            'user_id',
            'payment_status',
            'fulfilment_status',
            'closed',
            'placed_at',
            'channel_id',
            'tags',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'channel' => fn ($query) => $query->select('id', 'name'),
            'transactions' => fn ($query) => $query->select('id', 'order_id', 'reference'),
            'productLines' => fn ($query) => $query->select('id', 'order_id', 'description', 'identifier'),
            'addresses' => fn ($query) => $query->select(
                'id',
                'order_id',
                'country_id',
                'type',
                'first_name',
                'last_name',
                'company_name',
                'tax_identifier',
                'line_one',
                'line_two',
                'line_three',
                'city',
                'state',
                'postcode',
                'contact_email',
                'contact_phone',
            )->with([
                'country' => fn ($query) => $query->select('id', 'name'),
            ]),
            'tags' => fn ($query) => $query->select(
                $query->getModel()->qualifyColumn('id'),
                $query->getModel()->qualifyColumn('value'),
            ),
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $data = [
            'id' => (string) $model->id,
            'public_id' => (string) $model->public_id,
            'channel' => $model->channel->name,
            'reference' => $model->reference,
            'customer_reference' => $model->customer_reference,
            'payment_status' => (string) $model->payment_status,
            'fulfilment_status' => (string) $model->fulfilment_status,
            'closed' => $model->isClosed(),
            'placed_at' => optional($model->placed_at)->timestamp,
            'closed_at' => optional($model->closed_at)->timestamp,
            'created_at' => (int) $model->created_at->timestamp,
            'sub_total' => $model->sub_total,
            'total' => $model->total,
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
                'tax_identifier',
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
            $data["{$address->type}_fullname"] = $address->first_name.' '.$address->last_name;
        }

        $data['tags'] = $model->tags->pluck('value')->toArray();

        return $data;
    }
}
