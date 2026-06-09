<?php

namespace Lunar\Checkout\Contracts;

/**
 * A self-describing checkout step. Prototype slice of the spec 0001 element
 * model: the full model splits persistence/completion/visibility into opt-in
 * capability interfaces and writes through the cart API. This narrowed contract
 * is enough for a consumer to add a custom element that captures data into the
 * checkout session and renders via a frontend component, which is what the
 * "register a custom element" seam needs today.
 */
interface CheckoutElement
{
    /** Unique handle; the registry enforces uniqueness. */
    public function handle(): string;

    /** Display title (a translation key in the real model). */
    public function title(): string;

    /** Frontend component registry key — a rendering hint (see spec 0003). */
    public function component(): string;

    /** Layout region: 'main' (the form column) or 'summary' (the order rail). */
    public function region(): string;

    /** Inject the checkout session the element reads from and persists to. */
    public function setSession(CheckoutSession $session): static;

    /** Read-only hydration from already-persisted state. Must not write. */
    public function mount(): void;

    /**
     * Pure, idempotent props passed to the frontend component. No side effects.
     *
     * @return array<string, mixed>
     */
    public function props(): array;

    /**
     * The currently persisted values, used to seed the component.
     *
     * @return array<string, mixed>
     */
    public function data(): array;

    /**
     * Static validation rules for the incoming store payload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * Persist the validated subset to the checkout session.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): void;
}
