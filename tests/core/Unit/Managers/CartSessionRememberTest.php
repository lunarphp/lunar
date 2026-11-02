<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Lunar\Core\Managers\CartSessionManager;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

use function Pest\Laravel\actingAs;

uses(TestCase::class)->group('cart_session');
uses(RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create(['default' => true]);
    Channel::factory()->create(['default' => true]);

    Config::set('lunar.cart_session.remember.enabled', true);
});

function cartRememberCookieName(): string
{
    return config('lunar.cart_session.remember.cookie');
}

function queuedCartRememberCookie(): ?SymfonyCookie
{
    return Cookie::queued(cartRememberCookieName());
}

/**
 * Start a new request carrying the given remember cookie, optionally with an
 * expired session, and return a fresh manager for it.
 */
function cartRememberNextRequest(?string $token, bool $expireSession = true): CartSessionManager
{
    if ($expireSession) {
        Session::flush();
    }

    Cookie::flushQueuedCookies();

    request()->cookies->remove(cartRememberCookieName());

    if ($token) {
        request()->cookies->set(cartRememberCookieName(), $token);
    }

    return app(CartSessionManager::class);
}

function issueRememberedGuestCart(): array
{
    Config::set('lunar.cart_session.auto_create', true);

    $cart = app(CartSessionManager::class)->current();

    Config::set('lunar.cart_session.auto_create', false);

    return [$cart, queuedCartRememberCookie()->getValue()];
}

test('a guest cart is given a remember cookie holding a token whose hash is stored', function () {
    [$cart] = issueRememberedGuestCart();

    $cookie = queuedCartRememberCookie();

    expect($cookie)->not->toBeNull()
        ->and($cookie->getValue())->toHaveLength(40)
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->getTimestamp())
        ->and($cart->fresh()->remember_token)->toBe(hash('sha256', $cookie->getValue()));
});

test('a guest cart is restored from the cookie after the session expires', function () {
    [$cart, $token] = issueRememberedGuestCart();

    $this->travel(10)->days();

    $restored = cartRememberNextRequest($token)->current();

    expect($restored?->id)->toBe($cart->id)
        ->and(Session::get(config('lunar.cart_session.session_key')))->toBe($cart->id);
});

test('restoring re-sends the same token with a fresh expiry and touches the cart', function () {
    [$cart, $token] = issueRememberedGuestCart();

    $this->travel(10)->days();

    cartRememberNextRequest($token)->current();

    $cookie = queuedCartRememberCookie();

    expect($cookie?->getValue())->toBe($token)
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->getTimestamp())
        ->and($cart->fresh()->remember_token)->toBe(hash('sha256', $token))
        ->and($cart->fresh()->updated_at->isSameDay(now()))->toBeTrue();
});

test('a matching cookie with a live session costs no write and sets no cookie', function () {
    [$cart, $token] = issueRememberedGuestCart();

    $manager = cartRememberNextRequest($token, expireSession: false);

    DB::enableQueryLog();

    expect($manager->current()?->id)->toBe($cart->id);

    $writes = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], 'remember_token'));

    expect($writes)->toBeEmpty()
        ->and(queuedCartRememberCookie())->toBeNull();
});

test('the cookie is forgotten when it no longer points to a restorable cart', function (Closure $invalidate) {
    [$cart, $token] = issueRememberedGuestCart();

    $invalidate($cart);

    expect(cartRememberNextRequest($token)->current())->toBeNull();

    $cookie = queuedCartRememberCookie();

    expect($cookie)->not->toBeNull()
        ->and($cookie->getExpiresTime())->toBeLessThan(time());
})->with([
    'missing cart' => fn (Cart $cart) => $cart->forceDelete(),
    'merged cart' => fn (Cart $cart) => $cart->update([
        'merged_id' => Cart::factory()->create()->id,
    ]),
    'cart with a placed order' => fn (Cart $cart) => Order::factory()->create([
        'cart_id' => $cart->id,
        'placed_at' => now(),
    ]),
    'cart owned by a user' => fn (Cart $cart) => $cart->update([
        'user_id' => User::factory()->create()->id,
    ]),
]);

test('an unknown token is forgotten', function () {
    expect(cartRememberNextRequest('not-a-real-token')->current())->toBeNull()
        ->and(queuedCartRememberCookie()?->getExpiresTime())->toBeLessThan(time());
});

test('associating the cart with a user clears the token and the cookie', function () {
    [$cart, $token] = issueRememberedGuestCart();

    $manager = cartRememberNextRequest($token, expireSession: false);

    $manager->associate($cart, User::factory()->create(), 'merge');

    expect($cart->fresh()->remember_token)->toBeNull()
        ->and(queuedCartRememberCookie()?->getExpiresTime())->toBeLessThan(time());
});

test('forgetting the cart clears the token and the cookie', function () {
    Config::set('lunar.cart_session.delete_on_forget', false);

    [$cart, $token] = issueRememberedGuestCart();

    cartRememberNextRequest($token, expireSession: false)->forget();

    expect($cart->fresh()->remember_token)->toBeNull()
        ->and(queuedCartRememberCookie()?->getExpiresTime())->toBeLessThan(time());
});

test('a cart created for an authenticated user is not remembered', function () {
    actingAs(User::factory()->create());

    Config::set('lunar.cart_session.auto_create', true);

    $cart = app(CartSessionManager::class)->current();

    expect($cart->user_id)->not->toBeNull()
        ->and($cart->fresh()->remember_token)->toBeNull()
        ->and(queuedCartRememberCookie())->toBeNull();
});

test('nothing is remembered when remembering is disabled', function () {
    Config::set('lunar.cart_session.remember.enabled', false);
    Config::set('lunar.cart_session.auto_create', true);

    $cart = app(CartSessionManager::class)->current();

    expect($cart->fresh()->remember_token)->toBeNull()
        ->and(queuedCartRememberCookie())->toBeNull();
});

test('the remember cookie is excluded from cookie encryption', function () {
    $middleware = new EncryptCookies(app('encrypter'));

    expect($middleware->isDisabled(cartRememberCookieName()))->toBeTrue();
});
