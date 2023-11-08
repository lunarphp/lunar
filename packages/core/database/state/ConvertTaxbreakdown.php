<?php

namespace Lunar\Database\State;

use Illuminate\Support\Facades\Schema;
use Lunar\Facades\DB;

class ConvertTaxbreakdown
{
    public function prepare()
    {
        //
    }

    public function run()
    {
        $prefix = config('lunar.database.table_prefix');
        $updateTime = now();

        if ($this->canRunOnOrders()) {
            DB::table("{$prefix}orders")
                ->whereJsonContainsKey("{$prefix}orders.tax_breakdown->[0]->total")
                ->orderBy('id')
                ->chunk(500, function ($rows) use ($prefix, $updateTime) {
                    foreach ($rows as $row) {
                        $originalBreakdown = json_decode($row->tax_breakdown, true);

                        DB::table("{$prefix}orders")->where('id', '=', $row->id)->update([
                            'tax_breakdown' => collect($originalBreakdown)->map(function ($breakdown) use ($row) {
                                return [
                                    'description' => $breakdown['description'],
                                    'identifier' => $breakdown['identifier'] ?? $breakdown['description'],
                                    'percentage' => $breakdown['percentage'],
                                    'value' => $breakdown['total'],
                                    'currency_code' => $row->currency_code,
                                ];
                            })->toJson(),
                            'updated_at' => $updateTime,
                        ]);
                    }
                });
        }

        if ($this->canRunOnOrderLines()) {
            DB::table("{$prefix}order_lines")
                ->whereJsonContainsKey("{$prefix}order_lines.tax_breakdown->[0]->total")
                ->orderBy("{$prefix}order_lines.id")
                ->select(
                    "{$prefix}order_lines.id",
                    "{$prefix}order_lines.tax_breakdown",
                    "{$prefix}orders.currency_code",
                )
                ->join("{$prefix}orders", "{$prefix}order_lines.order_id", '=', "{$prefix}orders.id")
                ->chunk(500, function ($rows) use ($prefix, $updateTime) {
                    DB::transaction(function () use ($prefix, $updateTime, $rows) {
                        foreach ($rows as $row) {
                            $originalBreakdown = json_decode($row->tax_breakdown, true);

                            DB::table("{$prefix}order_lines")->where('id', '=', $row->id)->update([
                                'tax_breakdown' => collect($originalBreakdown)->map(function ($breakdown) use ($row) {
                                    return [
                                        'description' => $breakdown['description'],
                                        'identifier' => $breakdown['identifier'] ?? $breakdown['description'],
                                        'percentage' => $breakdown['percentage'],
                                        'value' => $breakdown['total'],
                                        'currency_code' => $row->currency_code,
                                    ];
                                })->toJson(),
                                'updated_at' => $updateTime,
                            ]);
                        }
                    });
                });
        }
    }

    protected function canRunOnOrders()
    {
        return $this->canRunOnTable('orders');
    }

    protected function canRunOnOrderLines()
    {
        return $this->canRunOnTable('order_lines');
    }

    protected function canRunOnTable(string $table)
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}{$table}") && DB::table("{$prefix}{$table}")->count();
    }
}
