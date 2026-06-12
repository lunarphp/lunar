<?php

namespace Lunar\Checkout\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Checkout\Contracts\Actions\ReconcilesCheckoutSession;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * The bounded PaymentProcessing reconciliation sweep + the sanctioned operator
 * stall exit (spec 0010 §F).
 *
 * Sweep: `lunar:checkout:reconcile` — resolves PaymentProcessing sessions
 * older than the configured window against the gateway's intent outcome.
 * Stalled sessions drop to a low-frequency tier (every Nth run via their
 * attempt counter staying maxed) and are skipped unless `--include-stalled`.
 *
 * Operator: `lunar:checkout:reconcile {uuid} --resolve=complete|refund|cancel`
 * — asserts the gateway outcome for a single stalled session.
 */
class ReconcileCheckoutSessions extends Command
{
    protected $signature = 'lunar:checkout:reconcile
        {uuid? : Reconcile a single session by uuid}
        {--resolve= : Operator resolution: complete, refund or cancel}
        {--include-stalled : Include sessions that have exhausted their attempts}';

    protected $description = 'Reconcile in-flight (PaymentProcessing) checkout sessions against their gateway outcome.';

    public function handle(ReconcilesCheckoutSession $reconcileCheckoutSession): int
    {
        if ($uuid = $this->argument('uuid')) {
            return $this->reconcileOne($reconcileCheckoutSession, $uuid);
        }

        if ($this->option('resolve')) {
            $this->error('--resolve requires a session uuid.');

            return self::FAILURE;
        }

        $outcomes = [];

        $query = CheckoutSession::query()->reconcilable();

        if (! $this->option('include-stalled')) {
            $maxAttempts = (int) config('lunar.checkout.reconciliation.max_attempts', 5);
            $query->where('reconciliation_attempts', '<', $maxAttempts);
        }

        $query->each(function (CheckoutSession $session) use ($reconcileCheckoutSession, &$outcomes): void {
            $outcome = $reconcileCheckoutSession->execute($session);
            $outcomes[$outcome] = ($outcomes[$outcome] ?? 0) + 1;
        });

        $this->info('Reconciled: '.(empty($outcomes) ? 'nothing to do.' : json_encode($outcomes)));

        return self::SUCCESS;
    }

    private function reconcileOne(ReconcilesCheckoutSession $reconcileCheckoutSession, string $uuid): int
    {
        $session = CheckoutSession::query()->where('uuid', $uuid)->first();

        if ($session === null) {
            $this->error("No checkout session found for uuid [{$uuid}].");

            return self::FAILURE;
        }

        $resolve = $this->option('resolve') ?: null;

        $outcome = $reconcileCheckoutSession->execute($session, $resolve);

        $this->info("Session [{$uuid}] resolved: {$outcome}.");

        return self::SUCCESS;
    }
}
