<?php

use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * These guard the "every page carries the extension seams" guarantee: content
 * pages must go through the shared scaffold, and the scaffold must actually
 * render the page-action ellipsis and a main slot zone. Auth and account pages
 * are standalone (no sectioned nav, no extension surface) and are exempt.
 */
function panelJsPath(string $relative): string
{
    return dirname(__DIR__, 3)."/packages/panel/resources/js/{$relative}";
}

it('builds every content page through the shared scaffold', function () {
    $pagesDir = panelJsPath('pages');
    $exemptTopLevel = ['auth', 'account'];

    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pagesDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        $relative = str_replace($pagesDir.'/', '', $file->getPathname());

        if (in_array(explode('/', $relative)[0], $exemptTopLevel, true)) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        if (! str_contains($contents, 'PageHeader') && ! str_contains($contents, 'SettingsShell')) {
            $offenders[] = $relative;
        }
    }

    expect($offenders)->toBe([]);
});

it('has scaffold components that carry the page-action ellipsis and a main slot zone', function () {
    $pageHeader = (string) file_get_contents(panelJsPath('components/PageHeader.vue'));
    $settingsShell = (string) file_get_contents(panelJsPath('layouts/SettingsShell.vue'));

    expect($pageHeader)->toContain('PageActions')
        ->and($settingsShell)->toContain('PageActions')
        ->and($settingsShell)->toContain('PageZone');
});
