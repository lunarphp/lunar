<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * A registered fulfilment method — the driver that owns a fulfilment's *flow*:
 * its state graph, which order lines it claims, and whether it carries carrier
 * tracking. A `Fulfilment` stores its method as a key (like a carrier key) and
 * resolves it through the {@see FulfilmentMethodManifest}. Core ships three
 * built on this seam (`shipping`, `collection`, `digital`); a consumer registers
 * their own (`prescription`, a 3PL hand-off, …) exactly as core registers these.
 */
interface FulfilmentMethod
{
    /**
     * The unique key identifying this method (e.g. "shipping").
     */
    public function getKey(): string;

    /**
     * The human-readable, translated method name, shown on the admin badge.
     */
    public function getLabel(): string;

    /**
     * The fulfilment states this flow uses.
     *
     * @return list<class-string<FulfilmentState>>
     */
    public function states(): array;

    /**
     * The legal transitions for this flow, as a from => [to, …] map.
     *
     * @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>
     */
    public function transitions(): array;

    /**
     * The state a fulfilment of this method starts in when created.
     *
     * @return class-string<FulfilmentState>
     */
    public function defaultState(): string;

    /**
     * The canonical "done" state `fulfil()` (and `ship()`) advances to.
     *
     * @return class-string<FulfilmentState>
     */
    public function fulfilledState(): string;

    /**
     * Which of the order's still-unclaimed fulfillable lines this method
     * covers. Called over a shrinking pool in priority order — see the manifest.
     *
     * @param  Collection<int, OrderLine>  $unclaimed
     * @return Collection<int, OrderLine>
     */
    public function claim(Order $order, Collection $unclaimed): Collection;

    /**
     * Assignment order: lower runs first; the catch-all `shipping` is highest.
     */
    public function priority(): int;

    /**
     * Whether fulfilments of this method carry carrier tracking.
     */
    public function usesTracking(): bool;
}
