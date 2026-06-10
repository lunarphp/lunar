<?php

namespace Lunar\Checkout\Support;

/**
 * Resolves the checkout app's OWN prebuilt bundle from the package's dist/
 * (spec 0008 §A). The app is self-contained: its assets ship prebuilt and are
 * streamed same-origin by the package's build route, so install-and-go needs no
 * Node, no Vite and no vendor:publish (spec 0008 §B).
 *
 * Reads the Vite manifest committed alongside dist/. Until `npm run build` has
 * produced a dist/, entry() returns empty and the root view shows a build hint.
 */
class CheckoutBundle
{
    public function __construct(
        private readonly string $distPath,
    ) {}

    public function distPath(): string
    {
        return $this->distPath;
    }

    /** @return array<string, mixed> */
    public function manifest(): array
    {
        $path = $this->distPath.'/.vite/manifest.json';

        if (! is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?: [];
    }

    /**
     * The hashed JS + CSS files for the app entry, ready for the root view.
     *
     * @return array{js: array<int, string>, css: array<int, string>}
     */
    public function entry(string $entry = 'resources/js/app.js'): array
    {
        $manifest = $this->manifest();

        if (! isset($manifest[$entry])) {
            return ['js' => [], 'css' => []];
        }

        return [
            'js' => [$manifest[$entry]['file']],
            'css' => $manifest[$entry]['css'] ?? [],
        ];
    }

    /** Traversal-safe absolute path to a built file inside dist/, or null. */
    public function file(string $file): ?string
    {
        $base = realpath($this->distPath);

        if ($base === false) {
            return null;
        }

        $full = realpath($base.'/'.$file);

        if ($full === false || ! str_starts_with($full, $base.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $full;
    }
}
