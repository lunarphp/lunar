<?php

namespace Lunar\Checkout\Contracts;

interface CheckoutAssets
{
    /**
     * Register a contributed element/gateway chunk (spec 0009 §C). One call is
     * the whole job: the chunk becomes servable same-origin and its module
     * <script> is emitted by the checkout root view. The contributor never
     * publishes the checkout app itself.
     *
     * @param  string  $package  unique key (e.g. "lunar-stripe"); also the URL segment
     * @param  string  $source   absolute path to the package's prebuilt chunk dir
     * @param  string  $entry     the self-registering ES module filename in $source
     * @param  string|null  $compat  SDK semver range the chunk was built against
     */
    public function register(string $package, string $source, string $entry = 'checkout.js', ?string $compat = null): void;

    /**
     * Every registered chunk, shaped for the root view's <script> loop.
     *
     * @return array<int, array{package: string, url: string, compat: string|null}>
     */
    public function all(): array;

    /**
     * Traversal-safe absolute path to a registered chunk file, or null when the
     * package is not registered or the file escapes its source dir. Backs the
     * same-origin asset route.
     */
    public function path(string $package, string $file): ?string;
}
