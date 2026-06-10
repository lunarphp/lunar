<?php

namespace Lunar\Checkout\Contracts;

interface CheckoutAssets
{
    /**
     * Register a contributed element/gateway chunk (spec 0009 §C). The chunk is
     * built with the Lunar checkout element preset (vendored Vue externals) +
     * the Laravel Vite plugin, into the host app's public/ dir; the checkout
     * root view renders it with Laravel's Vite class, which handles the dev
     * (hot file) vs build (manifest) switch — same as a Statamic addon.
     *
     * The contributor never publishes the checkout app itself; one call wires
     * the chunk into the page.
     *
     * @param  string  $package  unique key (e.g. "lunar-stripe")
     * @param  array{buildDirectory?: string, hotFile?: string, input: array<int, string>}  $config
     *                                                                                               Vite config: public build dir, dev hot file, entry point(s).
     */
    public function register(string $package, array $config): void;

    /**
     * Every registered chunk's Vite config, keyed by package, for the root view.
     *
     * @return array<string, array{buildDirectory: string, hotFile: string, input: array<int, string>}>
     */
    public function all(): array;
}
