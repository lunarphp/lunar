<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class TranslationsController
{
    /**
     * Serve every `panel::*` lang group for a locale as JSON, versioned by an
     * mtime-derived hash so the client can cache the payload in localStorage
     * and only refetch when a lang file actually changes.
     *
     * Reachable without authentication (registered in routes/auth.php) since
     * the login, two-factor, and password-reset screens need these strings
     * before a session exists.
     */
    public function __invoke(string $locale): JsonResponse
    {
        $langPath = dirname(__DIR__, 3).'/resources/lang';

        if (! File::isDirectory("{$langPath}/{$locale}")) {
            $locale = (string) config('app.fallback_locale', 'en');
        }

        [$messages, $mtimes] = $this->loadLocale("{$langPath}/{$locale}");

        ksort($messages);
        sort($mtimes);

        return response()->json([
            'version' => md5(implode('|', $mtimes)),
            'messages' => $messages,
        ]);
    }

    /**
     * @return array{0: array<string, array<string, string>>, 1: int[]}
     */
    protected function loadLocale(string $localePath): array
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

            $namespace = $file->getFilenameWithoutExtension();
            $messages[$namespace] = require $file->getPathname();
            $mtimes[] = $file->getMTime();
        }

        return [$messages, $mtimes];
    }
}
