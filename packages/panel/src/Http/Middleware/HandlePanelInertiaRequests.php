<?php

namespace Lunar\Panel\Http\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lunar\Panel\PanelManager;

class HandlePanelInertiaRequests extends Middleware
{
    protected $rootView = 'panel::app';

    public function __construct(protected PanelManager $manager) {}

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $user = $request->user($this->manager->guard());

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? $this->serializeUser($user) : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'panel' => [
                'name' => config('lunar.panel.name', 'Lunar'),
                'path' => config('lunar.panel.path', 'panel'),
                'storefront_url' => config('lunar.panel.storefront_url'),
                'support_url' => config('lunar.panel.support_url'),
            ],
            'locale' => fn () => app()->getLocale(),
            'navigation' => fn () => $this->manager->navigation()->toArray($user),
            'settingsNavigation' => fn () => $this->manager->settingsNavigation()->toArray($user, skipMenus: true),
            'slots' => fn () => $this->manager->slots()->forPage($this->currentPagePrefix($request), $user),
        ]);
    }

    protected function currentPagePrefix(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return str_starts_with($name, 'panel.') ? substr($name, strlen('panel.')) : $name;
    }

    /** @return array{id: mixed, name: string, email: string|null, admin: bool} */
    protected function serializeUser(Authenticatable $user): array
    {
        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return [
            'id' => $user->getAuthIdentifier(),
            'name' => $name !== '' ? $name : (string) ($user->email ?? ''),
            'email' => $user->email ?? null,
            'admin' => (bool) ($user->admin ?? false),
        ];
    }
}
