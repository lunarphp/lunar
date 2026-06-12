<?php

namespace Lunar\Checkout;

use Illuminate\Support\ServiceProvider;
use Lunar\Checkout\Contracts\Actions as Contracts;

/**
 * Binds every checkout action contract to its default implementation —
 * the canonical list of swappable action seams (spec 0016 conventions).
 * A consumer overrides one by binding the same contract in their own
 * service provider; config-string substitution is not supported.
 */
class ActionServiceProvider extends ServiceProvider
{
    /**
     * Action contract => default implementation.
     *
     * @var array<class-string, class-string>
     */
    protected array $actions = [
        Contracts\CreatesCheckoutSession::class => Actions\CreateCheckoutSession::class,
        Contracts\SyncsCheckoutSession::class => Actions\SyncCheckoutSession::class,
        Contracts\InvalidatesCheckoutSession::class => Actions\InvalidateCheckoutSession::class,
        Contracts\ReconcilesCheckoutSession::class => Actions\ReconcileCheckoutSession::class,
    ];

    public function register(): void
    {
        foreach ($this->actions as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
