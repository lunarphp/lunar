<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step: add the fulfilment choice columns.
 *
 * v2 records how each cart and order line is fulfilled (delivery or pickup),
 * which location a pickup is collected from, which methods a variant supports,
 * and an address for each location. v1 has none of these. A null method falls
 * back to one inferred from the shipping option, so no backfill is needed.
 *
 * Runs after backfill_stock_from_variants, which creates `locations` on a v1
 * database. Each column is guarded, so re-runs and already-v2 databases are
 * no-ops. One-way, no down().
 */
return new class extends Migration
{
    public function up(): void
    {
        $locations = $this->prefix.'locations';

        $this->addMissing('locations', [
            'line_one' => fn (Blueprint $table) => $table->string('line_one')->nullable(),
            'line_two' => fn (Blueprint $table) => $table->string('line_two')->nullable(),
            'line_three' => fn (Blueprint $table) => $table->string('line_three')->nullable(),
            'city' => fn (Blueprint $table) => $table->string('city')->nullable(),
            'state' => fn (Blueprint $table) => $table->string('state')->nullable(),
            'postcode' => fn (Blueprint $table) => $table->string('postcode')->nullable(),
            'country_id' => fn (Blueprint $table) => $table->foreignId('country_id')->nullable()->constrained($this->prefix.'countries')->nullOnDelete(),
            'contact_phone' => fn (Blueprint $table) => $table->string('contact_phone')->nullable(),
        ]);

        $this->addMissing('carts', [
            'fulfilment_method' => fn (Blueprint $table) => $table->string('fulfilment_method')->nullable()->index(),
            'pickup_location_id' => fn (Blueprint $table) => $table->foreignId('pickup_location_id')->nullable()->index()->constrained($locations)->nullOnDelete(),
        ]);

        $this->addMissing('cart_lines', [
            'fulfilment_method' => fn (Blueprint $table) => $table->string('fulfilment_method')->nullable()->index(),
        ]);

        $this->addMissing('orders', [
            'pickup_location_id' => fn (Blueprint $table) => $table->foreignId('pickup_location_id')->nullable()->index()->constrained($locations)->nullOnDelete(),
        ]);

        $this->addMissing('order_lines', [
            'fulfilment_method' => fn (Blueprint $table) => $table->string('fulfilment_method')->nullable()->index(),
        ]);

        $this->addMissing('product_variants', [
            'fulfilment_methods' => fn (Blueprint $table) => $table->jsonb('fulfilment_methods')->nullable(),
        ]);

        $this->addMissing('shipping_methods', [
            'pickup_location_id' => fn (Blueprint $table) => $table->foreignId('pickup_location_id')->nullable()->constrained($locations)->nullOnDelete(),
        ]);
    }

    /**
     * @param  array<string, callable(Blueprint): mixed>  $columns
     */
    protected function addMissing(string $table, array $columns): void
    {
        $name = $this->prefix.$table;

        if (! Schema::hasTable($name)) {
            return;
        }

        $missing = array_filter(
            $columns,
            fn (string $column): bool => ! Schema::hasColumn($name, $column),
            ARRAY_FILTER_USE_KEY,
        );

        if ($missing === []) {
            return;
        }

        Schema::table($name, function (Blueprint $blueprint) use ($missing) {
            foreach ($missing as $add) {
                $add($blueprint);
            }
        });
    }
};
