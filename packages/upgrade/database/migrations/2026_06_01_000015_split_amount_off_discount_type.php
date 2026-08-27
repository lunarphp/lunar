<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: split AmountOff into PercentageOff and FixedAmountOff.
 *
 * v1 and early v2 stored one AmountOff type whose `data.fixed_value` flag chose
 * between a percentage and a per-currency money amount. v2 has two types, so the
 * flag is read once here and then dropped, and `data.fixed_values` is renamed to
 * `data.amounts` to match FixedAmountOff.
 *
 * Runs after 000000_rewrite_lunar_class_strings, which has already rewritten the
 * stored `Lunar\DiscountTypes\AmountOff` to its `Lunar\Core` name — both spellings
 * are matched anyway so the step stands alone if that ordering ever changes.
 *
 * Rows are decoded and re-encoded in PHP rather than through JSON column functions,
 * which differ across the supported databases. Idempotent: a second run finds no
 * AmountOff rows left. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = $this->prefix.'discounts';

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->whereIn('type', [
                'Lunar\\Core\\DiscountTypes\\AmountOff',
                'Lunar\\DiscountTypes\\AmountOff',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($discounts) use ($table): void {
                foreach ($discounts as $discount) {
                    $data = json_decode($discount->data ?? '[]', true);

                    if (! is_array($data)) {
                        $data = [];
                    }

                    $isFixed = (bool) ($data['fixed_value'] ?? false);

                    if (array_key_exists('fixed_values', $data)) {
                        $data['amounts'] = $data['fixed_values'];
                        unset($data['fixed_values']);
                    }

                    unset($data['fixed_value']);

                    DB::table($table)->where('id', $discount->id)->update([
                        'type' => $isFixed
                            ? 'Lunar\\Core\\DiscountTypes\\FixedAmountOff'
                            : 'Lunar\\Core\\DiscountTypes\\PercentageOff',
                        'data' => json_encode($data),
                    ]);
                }
            });
    }
};
