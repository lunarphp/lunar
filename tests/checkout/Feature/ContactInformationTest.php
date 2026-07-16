<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Elements\ContactInformation;
use Lunar\Checkout\Session\CheckoutSession as SessionBag;
use Lunar\Tests\Checkout\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('describes itself as the contact region element', function () {
    $element = (new ContactInformation)->setSession(new SessionBag(app('session.store')));

    expect($element->handle())->toBe('contact')
        ->and($element->component())->toBe('contact-information')
        ->and($element->region())->toBe('contact');
});

it('projects guest contact state when not authenticated', function () {
    $element = (new ContactInformation)->setSession(new SessionBag(app('session.store')));
    $props = $element->props();

    expect($props['signedIn'])->toBeFalse()
        ->and($props['email'])->toBeNull()
        ->and($props['passkeysEnabled'])->toBeFalse();
});
