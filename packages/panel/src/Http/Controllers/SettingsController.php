<?php

namespace Lunar\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Lunar\Panel\PanelManager;

class SettingsController
{
    public function __construct(protected PanelManager $manager) {}

    /**
     * Stable settings entry point: redirects to the first settings page the
     * user can actually see (the permission-filtered tree, matching what the
     * sidebar renders), or the dashboard when none is visible.
     */
    public function __invoke(): RedirectResponse
    {
        $tree = $this->manager->settingsNavigation()->toArray($this->manager->user(), skipMenus: true);

        $url = $tree['groups'][0]['items'][0]['url']
            ?? $tree['items'][0]['url']
            ?? null;

        return $url ? redirect()->to($url) : redirect()->route('panel.dashboard');
    }
}
