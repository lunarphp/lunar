<?php

namespace Lunar\Checkout\Elements;

use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\ElementDataStore;

/**
 * Base for checkout elements. Provides the required-core defaults so a custom
 * element declares only what differs — typically handle(), title(), component()
 * and rules(). Persistence defaults to writing the validated payload into the
 * checkout session under the element's handle; data() reads it back.
 */
abstract class AbstractCheckoutElement implements CheckoutElement
{
    protected ElementDataStore $dataStore;

    public function setDataStore(ElementDataStore $store): static
    {
        $this->dataStore = $store;

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
        return (array) $this->dataStore->get($this->handle(), []);
    }

    public function rules(): array
    {
        return [];
    }

    public function store(array $data): void
    {
        $this->dataStore->put($this->handle(), $data);
    }
}
