<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Contracts\ElementRegistry;
use Lunar\Checkout\Elements\AbstractCheckoutElement;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\Session\ModelElementStore;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Checkout\TestCase;
use Lunar\Tests\Checkout\Utils\CheckoutCart;
use Lunar\Tests\Core\Stubs\User;

uses(TestCase::class, RefreshDatabase::class);

it('round-trips captured data through the session row', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $store = new ModelElementStore($session);
    $store->put('order-details', ['reference' => 'PO-1234']);

    expect($session->fresh()->getElementData('order-details'))->toBe(['reference' => 'PO-1234'])
        ->and((new ModelElementStore($session->fresh()))->get('order-details'))->toBe(['reference' => 'PO-1234']);
});

it('does not lose a sibling handle when two stores write', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    /*
     * Both instances are loaded BEFORE either writes — that ordering is the
     * whole test. Two requests each hold a model read at their own start, and
     * the bag is one JSON column, so an unlocked read-modify-write has the
     * second save a blob built from its own stale (empty) read and drop alpha.
     * Loading them sequentially after the first write would pass either way
     * and prove nothing.
     */
    $first = CheckoutSession::find($session->id);
    $second = CheckoutSession::find($session->id);

    (new ModelElementStore($first))->put('alpha', ['a' => 1]);
    (new ModelElementStore($second))->put('beta', ['b' => 2]);

    $bag = $session->fresh()->element_data->getArrayCopy();

    expect($bag)->toHaveKeys(['alpha', 'beta'])
        ->and($bag['alpha'])->toBe(['a' => 1])
        ->and($bag['beta'])->toBe(['b' => 2]);
});

it('lets the session owner post an element and land it on the row', function () {
    // The route now goes {session}/elements/{handle} instead of a bare
    // {handle} — this is what proves an OWNER can still POST through it and
    // have the payload persisted, not just that a stranger is blocked.
    app(ElementRegistry::class)->add(new class extends AbstractCheckoutElement
    {
        public function handle(): string
        {
            return 'order-details';
        }

        public function title(): string
        {
            return 'Order details';
        }

        public function component(): string
        {
            return 'order-details';
        }

        public function rules(): array
        {
            return ['reference' => ['required', 'string']];
        }
    });

    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $this->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [
        'reference' => 'PO-9999',
    ])->assertRedirect();

    expect($session->fresh()->getElementData('order-details'))->toBe(['reference' => 'PO-9999']);
});

it('leaves the model instance clean after a bag write', function () {
    // element_data is synced back to $this after the locked write. Without
    // that sync, $this stays dirty on the pre-write bag, and a later
    // unrelated save() on the same instance would rewrite the whole column
    // from that stale snapshot, outside the lock the write just took.
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    (new ModelElementStore($session))->put('order-details', ['reference' => 'PO-1']);

    expect($session->isDirty('element_data'))->toBeFalse();

    $session->customer_email = 'later@example.test';
    $session->save();

    expect($session->fresh()->getElementData('order-details'))->toBe(['reference' => 'PO-1']);
});

it('forgets a handle', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $store = new ModelElementStore($session);
    $store->put('order-details', ['reference' => 'PO-1']);
    $store->forget('order-details');

    expect($session->fresh()->getElementData('order-details'))->toBeNull();
});

it('forbids storing an element against another customer\'s session', function () {
    $cart = CheckoutCart::orderable();
    CartSession::use($cart);
    $session = app(CheckoutDriver::class)->resolveOrCreateSession($cart);

    $session->update(['customer_reference' => (string) Customer::factory()->create()->id]);

    $intruder = User::factory()->create();
    $intruderCustomer = Customer::factory()->create();
    $intruder->customers()->attach($intruderCustomer);

    // A different cart than the session's, so ensureOwnership falls through to
    // the customer_reference fallback rather than short-circuiting on a cart
    // id the test session still has bound from CartSession::use() above.
    $otherCart = Cart::factory()->create([
        'channel_id' => $cart->channel_id,
        'currency_id' => $cart->currency_id,
    ]);
    CartSession::use($otherCart);

    $this->actingAs($intruder)
        ->post(route('lunar.checkout.elements.store', ['session' => $session->uuid, 'handle' => 'order-details']), [])
        ->assertForbidden();
});
