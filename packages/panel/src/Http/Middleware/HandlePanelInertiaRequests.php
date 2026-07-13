<?php

namespace Lunar\Panel\Http\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
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
            'pageId' => fn () => $this->currentPagePrefix($request),
            'slots' => fn () => $this->manager->slots()->forPage($this->currentPagePrefix($request), $user),
            'pageActions' => fn () => $this->manager
                ->resolvePageActions($this->currentPagePrefix($request))
                ->resolve($this->currentRecord($request)),
        ]);
    }

    protected function currentPagePrefix(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return str_starts_with($name, 'panel.') ? substr($name, strlen('panel.')) : $name;
    }

    /**
     * The primary record bound to the current route (e.g. the Customer on
     * `customers.edit`), passed to page actions so they can build per-record
     * URLs. Listing pages have no bound model and resolve with null.
     */
    protected function currentRecord(Request $request): ?Model
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return $parameter;
            }
        }

        return null;
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
