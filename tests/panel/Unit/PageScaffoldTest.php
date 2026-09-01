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

it('sets a browser-tab title on every page', function () {
    $pagesDir = panelJsPath('pages');

    // Content pages inherit <Head> from PageHeader/SettingsShell (asserted in
    // the scaffold test below); the exempt auth and account pages must render
    // their own.
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

        if (! in_array(explode('/', $relative)[0], $exemptTopLevel, true)) {
            continue;
        }

        if (! str_contains((string) file_get_contents($file->getPathname()), '<Head ')) {
            $offenders[] = $relative;
        }
    }

    expect($offenders)->toBe([]);
});

it('has scaffold components that carry the page-action ellipsis and a main slot zone', function () {
    $pageHeader = (string) file_get_contents(panelJsPath('components/PageHeader.vue'));
    $settingsShell = (string) file_get_contents(panelJsPath('layouts/SettingsShell.vue'));

    // SettingsShell renders the browser-tab <Head> through PageHeader, so the
    // scaffold still owns the title on every settings page.
    expect($pageHeader)->toContain('PageActions')
        ->and($pageHeader)->toContain('<Head ')
        ->and($settingsShell)->toContain('PageActions')
        ->and($settingsShell)->toContain('PageZone')
        ->and($settingsShell)->toContain('<PageHeader ');
});

/**
 * Save and discard live in the sticky breadcrumb bar, not in the page header,
 * so they stay reachable on a long form. Every draft-backed edit page follows
 * this; a page that puts DraftActions in its PageHeader scrolls them away.
 */
it('puts the draft actions in the sticky breadcrumb bar on every draft-backed page', function () {
    $pagesDir = panelJsPath('pages');

    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pagesDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        // Markup only — the script block mentions <DraftActions /> in comments.
        $template = mb_strrpos($contents, '</script>');
        $markup = $template === false ? $contents : mb_substr($contents, $template);

        if (! str_contains($markup, '<DraftActions')) {
            continue;
        }

        $relative = str_replace($pagesDir.'/', '', $file->getPathname());

        // Breadcrumbs is the scaffold's only sticky region, so every rendered
        // DraftActions has to sit inside one.
        preg_match_all('/<Breadcrumbs\b.*?<\/Breadcrumbs>/s', $markup, $blocks);

        $inBreadcrumbs = substr_count(implode('', $blocks[0]), '<DraftActions');

        if ($inBreadcrumbs !== substr_count($markup, '<DraftActions')) {
            $offenders[] = $relative;
        }
    }

    expect($offenders)->toBe([]);
});

/**
 * Toggle takes `:on` and emits `toggle`; it has no modelValue prop. Bound with
 * v-model it renders permanently off, swallows the click, and leaks modelValue
 * through to Reka's SwitchRoot, which then writes back on mount and leaves a
 * pristine form reading as dirty.
 */
it('never binds the Toggle component with v-model', function () {
    $jsDir = panelJsPath('');

    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($jsDir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'vue') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        if (preg_match('/<Toggle\b[^>]*\bv-model\b/', $contents)) {
            $offenders[] = str_replace($jsDir, '', $file->getPathname());
        }
    }

    expect($offenders)->toBe([]);
});
