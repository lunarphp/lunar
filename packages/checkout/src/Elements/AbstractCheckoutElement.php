<?php

namespace Lunar\Checkout\Elements;

use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\CheckoutSession;

/**
 * Base for checkout elements. Provides the required-core defaults so a custom
 * element declares only what differs — typically handle(), title(), component()
 * and rules(). Persistence defaults to writing the validated payload into the
 * checkout session under the element's handle; data() reads it back.
 */
abstract class AbstractCheckoutElement implements CheckoutElement
{
    protected CheckoutSession $session;

    public function setSession(CheckoutSession $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function region(): string
    {
        return 'main';
    }

    public function mount(): void
    {
        // Read-only by default. Override to seed in-memory defaults from context.
    }

    public function props(): array
    {
        return [];
    }

    public function data(): array
    {
        return (array) $this->session->get($this->handle(), []);
    }

    public function rules(): array
    {
        return [];
    }

    public function store(array $data): void
    {
        $this->session->put($this->handle(), $data);
    }
}
