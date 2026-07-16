<?php

namespace Lunar\Checkout\Elements;

use Laravel\Fortify\Features;

/**
 * The default contact-information checkout step: captures the customer's email
 * and drives inline sign-in for an existing account. Rendering + projection
 * only — persistence goes through the uuid-scoped contact controller actions
 * against the Eloquent CheckoutSession model, not the element bag (the two are
 * disconnected). Provided by the package; the host registers it (the registry
 * rejects duplicate handles, so a consumer swaps by registering a different
 * element for the `contact` region).
 */
class ContactInformation extends AbstractCheckoutElement
{
    public function handle(): string
    {
        return 'contact';
    }

    public function title(): string
    {
        return 'Contact information';
    }

    public function component(): string
    {
        return 'contact-information';
    }

    public function region(): string
    {
        return 'contact';
    }

    /**
     * @return array{signedIn: bool, displayName: string|null, email: string|null, passkeysEnabled: bool}
     */
    public function props(): array
    {
        $user = auth()->user();

        return [
            'signedIn' => $user !== null,
            'displayName' => $user?->name,
            'email' => $user?->email,
            'passkeysEnabled' => $this->passkeysEnabled(),
        ];
    }

    private function passkeysEnabled(): bool
    {
        return class_exists(Features::class)
            && Features::enabled(Features::passkeys());
    }
}
