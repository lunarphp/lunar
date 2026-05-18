<?php

namespace Lunar\Shipping\Database\State;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateCutoffToSchedule
{
    public function prepare(): void
    {
        if (! $this->cutoffColumnExists()) {
            return;
        }

        $prefix = config('lunar.database.table_prefix');

        DB::table("{$prefix}shipping_methods")
            ->whereNotNull('cutoff')
            ->orderBy('id')
            ->chunk(100, function ($methods) use ($prefix) {
                foreach ($methods as $method) {
                    $data = json_decode($method->data ?? '{}', true) ?? [];

                    if (! empty($data['schedule'])) {
                        continue;
                    }

                    // Strip seconds from "H:i:s" to get "H:i"
                    $to = substr($method->cutoff, 0, 5);

                    $schedule = [];
                    for ($day = 1; $day <= 7; $day++) {
                        $schedule[(string) $day] = [
                            'enabled' => true,
                            'from' => null,
                            'to' => $to,
                        ];
                    }

                    $data['schedule'] = $schedule;

                    DB::table("{$prefix}shipping_methods")
                        ->where('id', $method->id)
                        ->update([
                            'data' => json_encode($data),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function run(): void
    {
        //
    }

    protected function cutoffColumnExists(): bool
    {
        $prefix = config('lunar.database.table_prefix');

        return Schema::hasTable("{$prefix}shipping_methods")
            && Schema::hasColumn("{$prefix}shipping_methods", 'cutoff');
    }
}
