<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Translation\FileLoader;
use Lunar\Panel\PanelManager;

class TranslationsController
{
    public function __construct(protected PanelManager $manager) {}

    /**
     * Serve the panel's lang groups for a locale as JSON, versioned by an
     * mtime-derived hash so the client can cache the payload in localStorage
     * and only refetch when a lang file actually changes.
     *
     * The panel's own groups are served under their bare group name (`auth`,
     * `nav`, …). Add-on namespaces registered via `Panel::translations()` (or
     * `Section::langNamespaces()`) are appended as `{namespace}::{group}`
     * keys — mirroring Laravel's own namespaced trans syntax — with a
     * per-namespace fallback to the app fallback locale, so an add-on that
     * ships fewer locales than the panel still renders.
     *
     * Reachable without authentication (registered in routes/auth.php) since
     * the login, two-factor, and password-reset screens need these strings
     * before a session exists.
     */
    public function __invoke(string $locale): JsonResponse
    {
        $fallback = (string) config('app.fallback_locale', 'en');
        $panelLangPath = dirname(__DIR__, 3).'/resources/lang';

        if (! File::isDirectory("{$panelLangPath}/{$locale}")) {
            $locale = $fallback;
        }

        [$messages, $mtimes] = $this->loadGroups("{$panelLangPath}/{$locale}");

        foreach ($this->namespaceHints() as $namespace => $hint) {
            $path = File::isDirectory("{$hint}/{$locale}") ? "{$hint}/{$locale}" : "{$hint}/{$fallback}";

            [$namespaceMessages, $namespaceMtimes] = $this->loadGroups($path);

            foreach ($namespaceMessages as $group => $groupMessages) {
                $messages["{$namespace}::{$group}"] = $groupMessages;
            }

            $mtimes = [...$mtimes, ...$namespaceMtimes];
        }

        ksort($messages);
        sort($mtimes);

        return response()->json([
            'version' => md5(implode('|', $mtimes)),
            'messages' => $messages,
        ]);
    }

    /**
     * Lang paths for the registered add-on namespaces, resolved from the
     * translator's namespace hints. A registered namespace with no hint is
     * skipped rather than erroring — the add-on's provider may simply not
     * call loadTranslationsFrom().
     *
     * @return array<string, string>
     */
    protected function namespaceHints(): array
    {
        $loader = app('translator')->getLoader();

        if (! $loader instanceof FileLoader) {
            return [];
        }

        return array_intersect_key(
            $loader->namespaces(),
            array_flip($this->manager->translationNamespaces()),
        );
    }

    /**
     * @return array{0: array<string, array<string, string>>, 1: int[]}
     */
    protected function loadGroups(string $localePath): array
    {
        $messages = [];
        $mtimes = [];

        if (! File::isDirectory($localePath)) {
            return [$messages, $mtimes];
        }

        foreach (File::files($localePath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $group = $file->getFilenameWithoutExtension();
            $messages[$group] = require $file->getPathname();
            $mtimes[] = $file->getMTime();
        }

        return [$messages, $mtimes];
    }
}
