<?php

namespace Lunar\Checkout\Contracts;

/**
 * Presentation + capability descriptor for one way to pay (spec 0002 §A).
 * A method owns no money movement: it names a gateway driver registered with
 * Lunar's Payments manager and tells the checkout how to render and route it.
 * Core checkout ships no methods — gateway packages or the host register them.
 */
interface PaymentMethod
{
    public function handle(): string;

    /**
     * Translation key / display label for the method tab.
     */
    public function label(): string;

    /**
     * Key of a PaymentType driver registered with the Payments manager.
     */
    public function driver(): string;

    /**
     * Whether a pre-confirmation intent is required. True routes the session
     * Open → PaymentProcessing at the pay boundary; false is the synchronous
     * Open → Completed path (spec 0004 §C).
     */
    public function requiresIntent(): bool;

    /**
     * Frontend component hint resolved by the checkout app's registry.
     */
    public function component(): string;

    /**
     * Client-safe configuration the frontend component needs (publishable
     * keys, locale hints…). Never secrets — this is projected to the page.
     *
     * @return array<string, mixed>
     */
    public function config(): array;

    /**
     * Whether the method also renders in the express-wallet region.
     */
    public function supportsExpress(): bool;

    public function expressComponent(): ?string;
}
