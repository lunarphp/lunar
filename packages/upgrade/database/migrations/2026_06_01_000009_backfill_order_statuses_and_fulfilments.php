<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Lunar\Core\Database\Migration;

/**
 * v1 → v2 upgrade data step (spec 0022): replace the hand-driven v1 headline
 * `orders.status` with the two derived rollups and the open/closed archive,
 * and materialise fulfilments for historical shipped orders.
 *
 * - `payment_status` derives from the transaction ledger (spec 0022 section B,
 *   mirroring Actions\Orders\ResolvePaymentStatus).
 * - `fulfilment_status` maps from the v1 headline via the configurable
 *   `lunar.upgrade.orders.fulfilled_statuses` list. Each fulfilled order gets
 *   one whole-order `Fulfilment` (state `shipped`, default location,
 *   `shipped_at` from `placed_at`) covering every fulfillable line at full
 *   quantity — the rollup is recomputed from those rows, so a bare status
 *   backfill would not survive. Orders with nothing to fulfil resolve to
 *   `fulfilled` with no fulfilment, matching ResolveFulfilmentStatus.
 * - `closed_at` / `cancelled_at` stamp from the configurable closed/cancelled
 *   lists, then the v1 `status` column is dropped.
 *
 * v1 headline statuses are free-form per store; the default lists cover the
 * stock v1 statuses and stores override them in the published upgrade config.
 *
 * Self-sufficient: the v2 baseline that adds the order columns and fulfilment
 * tables is marked-run by the ledger rewrite, so the schema delta is applied
 * here (guarded, mirroring the baseline). The add_public_id step has already
 * run by this point, so the fulfilment tables are created with `public_id`
 * and rows mint their own ULIDs. Re-runs and already-v2 databases are no-ops.
 * There is no `down()` — upgrade data migrations are one-way; recover from a
 * backup.
 */
return new class extends Migration
{
    public function up(): void
    {
        $orders = $this->prefix.'orders';

        // The v1 headline is the signal; absent means already v2.
        if (! Schema::hasTable($orders) || ! Schema::hasColumn($orders, 'status')) {
            return;
        }

        $this->ensureOrderColumns($orders);
        $this->ensureLocationsTable();
        $this->ensureFulfilmentTables();

        /** @var array{fulfilled_statuses?: list<string>, closed_statuses?: list<string>, cancelled_statuses?: list<string>} $config */
        $config = config('lunar.upgrade.orders', []);
        $fulfilled = $config['fulfilled_statuses'] ?? ['dispatched', 'complete'];
        $closed = $config['closed_statuses'] ?? ['complete', 'cancelled', 'refunded'];
        $cancelled = $config['cancelled_statuses'] ?? ['cancelled'];

        $this->backfillFulfilmentStatus($orders, $fulfilled);
        $this->createFulfilments($orders, $fulfilled);
        $this->derivePaymentStatus($orders);
        $this->stampTimestamps($orders, $closed, $cancelled);

        Schema::table($orders, function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Add the v2 order columns if the baseline migrations have not (mirrors
     * ..._create_orders_table); v1 had only the headline `status`. All the
     * missing columns go in one Schema::table call — one ALTER, one table
     * rebuild, on what is usually the largest table in the database.
     */
    protected function ensureOrderColumns(string $orders): void
    {
        $columns = [
            'payment_status' => fn (Blueprint $table) => $table->string('payment_status')->default('pending')->index(),
            'fulfilment_status' => fn (Blueprint $table) => $table->string('fulfilment_status')->default('unfulfilled')->index(),
            'closed_at' => fn (Blueprint $table) => $table->dateTime('closed_at')->nullable()->index(),
            'cancelled_at' => fn (Blueprint $table) => $table->dateTime('cancelled_at')->nullable()->index(),
            'cancel_reason' => fn (Blueprint $table) => $table->string('cancel_reason')->nullable(),
            'cancel_note' => fn (Blueprint $table) => $table->text('cancel_note')->nullable(),
        ];

        $missing = array_filter(
            array_keys($columns),
            fn (string $column): bool => ! Schema::hasColumn($orders, $column),
        );

        if ($missing === []) {
            return;
        }

        Schema::table($orders, function (Blueprint $table) use ($columns, $missing) {
            foreach ($missing as $column) {
                $columns[$column]($table);
            }
        });
    }

    /**
     * Create the locations table if an earlier step has not (mirrors
     * ..._create_locations_table); fulfilments require a location.
     */
    protected function ensureLocationsTable(): void
    {
        if (Schema::hasTable($this->prefix.'locations')) {
            return;
        }

        Schema::create($this->prefix.'locations', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name');
            $table->string('handle')->unique();
            $table->boolean('default')->default(false)->index();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Create the fulfilment tables if the baseline migrations have not
     * (mirrors ..._create_fulfilment{s,_lines,_trackings}_table).
     */
    protected function ensureFulfilmentTables(): void
    {
        if (! Schema::hasTable($this->prefix.'fulfilments')) {
            Schema::create($this->prefix.'fulfilments', function (Blueprint $table) {
                $table->id();
                $table->ulid('public_id')->unique();
                $table->foreignId('order_id')->constrained($this->prefix.'orders')->cascadeOnDelete();
                $table->foreignId('location_id')->constrained($this->prefix.'locations')->restrictOnDelete();
                $table->string('reference')->nullable()->index();
                $table->string('method')->default('shipping')->index()->comment('Fulfilment method key, resolved via FulfilmentMethodManifest');
                $table->string('state')->default('pending')->index();
                $table->text('notes')->nullable();
                $table->jsonb('meta')->nullable();
                $table->dateTime('shipped_at')->nullable()->index();
                $table->dateTime('held_at')->nullable()->index();
                $table->string('hold_reason')->nullable();
                $table->text('hold_note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($this->prefix.'fulfilment_lines')) {
            Schema::create($this->prefix.'fulfilment_lines', function (Blueprint $table) {
                $table->id();
                $table->ulid('public_id')->unique();
                $table->foreignId('fulfilment_id')->constrained($this->prefix.'fulfilments')->cascadeOnDelete();
                $table->foreignId('order_line_id')->constrained($this->prefix.'order_lines')->cascadeOnDelete();
                $table->unsignedInteger('quantity');
                $table->timestamps();
                $table->unique(['fulfilment_id', 'order_line_id']);
            });
        }

        if (! Schema::hasTable($this->prefix.'fulfilment_trackings')) {
            Schema::create($this->prefix.'fulfilment_trackings', function (Blueprint $table) {
                $table->id();
                $table->ulid('public_id')->unique();
                $table->foreignId('fulfilment_id')->constrained($this->prefix.'fulfilments')->cascadeOnDelete();
                $table->string('carrier')->nullable();
                $table->string('shipping_method')->nullable();
                $table->string('tracking_number')->nullable();
                $table->string('tracking_url')->nullable();
                $table->jsonb('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * @param  list<string>  $fulfilled
     */
    protected function backfillFulfilmentStatus(string $orders, array $fulfilled): void
    {
        DB::table($orders)
            ->whereIn('status', $fulfilled)
            ->update(['fulfilment_status' => 'fulfilled']);

        // Nothing to fulfil — settled by definition (mirrors
        // ResolveFulfilmentStatus's empty-lines rule).
        $lines = $this->prefix.'order_lines';

        if (! Schema::hasTable($lines) || ! Schema::hasColumn($lines, 'requires_fulfilment')) {
            return;
        }

        DB::table($orders)
            ->whereNotExists(function ($query) use ($lines, $orders) {
                $query->select(DB::raw(1))
                    ->from($lines)
                    ->whereColumn($lines.'.order_id', $orders.'.id')
                    ->where($lines.'.requires_fulfilment', true);
            })
            ->update(['fulfilment_status' => 'fulfilled']);
    }

    /**
     * One whole-order shipped fulfilment per fulfilled v1 order, covering its
     * fulfillable lines at full quantity. Chunked bulk inserts; ULIDs minted
     * from the ship time so backfilled ids sort chronologically.
     *
     * @param  list<string>  $fulfilled
     */
    protected function createFulfilments(string $orders, array $fulfilled): void
    {
        $lines = $this->prefix.'order_lines';
        $fulfilments = $this->prefix.'fulfilments';
        $fulfilmentLines = $this->prefix.'fulfilment_lines';

        if (! Schema::hasTable($lines) || ! Schema::hasColumn($lines, 'requires_fulfilment')) {
            return;
        }

        $locationId = $this->defaultLocationId();

        DB::table($orders)
            ->whereIn('status', $fulfilled)
            ->whereNotExists(function ($query) use ($fulfilments, $orders) {
                $query->select(DB::raw(1))
                    ->from($fulfilments)
                    ->whereColumn($fulfilments.'.order_id', $orders.'.id');
            })
            ->orderBy('id')
            ->select(['id', 'placed_at', 'created_at'])
            ->chunkById(500, function ($chunk) use ($lines, $fulfilments, $fulfilmentLines, $locationId) {
                $lineRows = DB::table($lines)
                    ->whereIn('order_id', $chunk->pluck('id'))
                    ->where('requires_fulfilment', true)
                    ->get(['id', 'order_id', 'quantity'])
                    ->groupBy('order_id');

                // Orders with no fulfillable lines get no (empty) fulfilment.
                $shippable = $chunk->filter(fn (object $order): bool => $lineRows->has($order->id));

                if ($shippable->isEmpty()) {
                    return;
                }

                // Timestamps are seeded from the ship time, not the migration
                // run, so created_at ordering reflects history — matching the
                // ULID treatment in the add_public_id step.
                DB::table($fulfilments)->insert($shippable->map(fn (object $order): array => [
                    'public_id' => (string) Str::ulid(Carbon::parse($this->shippedAt($order))),
                    'order_id' => $order->id,
                    'location_id' => $locationId,
                    'method' => 'shipping',
                    'state' => 'shipped',
                    'shipped_at' => $this->shippedAt($order),
                    'created_at' => $this->shippedAt($order),
                    'updated_at' => $this->shippedAt($order),
                ])->all());

                $fulfilmentIds = DB::table($fulfilments)
                    ->whereIn('order_id', $shippable->pluck('id'))
                    ->pluck('id', 'order_id');

                $inserts = $shippable->flatMap(fn (object $order) => $lineRows[$order->id]->map(fn (object $line): array => [
                    'public_id' => (string) Str::ulid(Carbon::parse($this->shippedAt($order))),
                    'fulfilment_id' => $fulfilmentIds[$order->id],
                    'order_line_id' => $line->id,
                    'quantity' => $line->quantity,
                    'created_at' => $this->shippedAt($order),
                    'updated_at' => $this->shippedAt($order),
                ]));

                foreach ($inserts->chunk(500) as $batch) {
                    DB::table($fulfilmentLines)->insert($batch->all());
                }
            });
    }

    /**
     * Roll the transaction ledger up into the Shopify-style payment status,
     * mirroring Actions\Orders\ResolvePaymentStatus. Orders resolving to
     * `pending` keep the column default.
     */
    protected function derivePaymentStatus(string $orders): void
    {
        $transactions = $this->prefix.'transactions';
        $hasLedger = Schema::hasTable($transactions);

        DB::table($orders)
            ->orderBy('id')
            ->select(['id', 'total'])
            ->chunkById(500, function ($chunk) use ($orders, $transactions, $hasLedger) {
                $ledgers = $hasLedger
                    ? DB::table($transactions)
                        ->whereIn('order_id', $chunk->pluck('id'))
                        ->selectRaw(
                            'order_id, '
                            ."sum(case when type = 'capture' and success = ? then amount else 0 end) as captured, "
                            ."sum(case when type = 'refund' and success = ? then amount else 0 end) as refunded, "
                            ."max(case when type = 'intent' and success = ? then 1 else 0 end) as authorized",
                            [true, true, true],
                        )
                        ->groupBy('order_id')
                        ->get()
                        ->keyBy('order_id')
                    : collect();

                $statuses = [];

                foreach ($chunk as $order) {
                    $ledger = $ledgers->get($order->id);
                    $captured = (int) ($ledger->captured ?? 0);
                    $refunded = (int) ($ledger->refunded ?? 0);
                    $authorized = (bool) ($ledger->authorized ?? false);
                    $total = (int) $order->total;

                    $status = match (true) {
                        $captured > 0 && $refunded >= $captured => 'refunded',
                        $captured >= $total && $refunded > 0 => 'partially-refunded',
                        $captured >= $total && $captured > 0 => 'paid',
                        // A zero-total order with no refunds settles as paid.
                        $total === 0 && $refunded === 0 => 'paid',
                        $captured > 0 => 'partially-paid',
                        $authorized => 'authorized',
                        $ledger !== null => 'voided',
                        default => 'pending',
                    };

                    if ($status !== 'pending') {
                        $statuses[$status][] = $order->id;
                    }
                }

                foreach ($statuses as $status => $ids) {
                    DB::table($orders)->whereIn('id', $ids)->update(['payment_status' => $status]);
                }
            });
    }

    /**
     * @param  list<string>  $closed
     * @param  list<string>  $cancelled
     */
    protected function stampTimestamps(string $orders, array $closed, array $cancelled): void
    {
        DB::table($orders)
            ->whereIn('status', $closed)
            ->whereNull('closed_at')
            ->update(['closed_at' => DB::raw('coalesce(updated_at, placed_at, created_at)')]);

        DB::table($orders)
            ->whereIn('status', $cancelled)
            ->whereNull('cancelled_at')
            ->update(['cancelled_at' => DB::raw('coalesce(updated_at, placed_at, created_at)')]);
    }

    protected function shippedAt(object $order): string
    {
        return $order->placed_at ?? $order->created_at ?? now()->format('Y-m-d H:i:s');
    }

    protected function defaultLocationId(): int
    {
        $locations = $this->prefix.'locations';

        $id = DB::table($locations)->where('default', true)->value('id')
            ?? DB::table($locations)->orderBy('id')->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $row = [
            'name' => 'Default',
            'handle' => 'default',
            'default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($locations, 'public_id')) {
            $row['public_id'] = (string) Str::ulid();
        }

        return (int) DB::table($locations)->insertGetId($row);
    }
};
