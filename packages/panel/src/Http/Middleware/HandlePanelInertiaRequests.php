<?php

namespace Lunar\Panel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lunar\Panel\Forms\FormSlices;
use Lunar\Panel\PanelManager;
use Lunar\Panel\Support\Gravatar;

class HandlePanelInertiaRequests extends Middleware
{
    protected $rootView = 'panel::app';

    public function __construct(
        protected PanelManager $manager,
        protected FormSlices $formSlices,
    ) {}

    /**
     * Record each Inertia page visit as the session's previous URL. Laravel
     * records only full page loads, and Inertia visits are XHR, so an action
     * answering with back() from a browser that sends no Referer header
     * (privacy settings, some embedded views) would land on the site root
     * instead of the page it came from.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && $request->header('X-Inertia') && ! $request->prefetch() && $request->hasSession()) {
            $request->session()->setPreviousUrl($request->fullUrl());
        }

        return parent::handle($request, $next);
    }

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
                'info' => fn () => $request->session()->get('info'),
            ],
            'panel' => [
                'name' => config('lunar.panel.name', 'Lunar'),
                'path' => config('lunar.panel.path', 'panel'),
                'storefront_url' => config('lunar.panel.storefront_url'),
                'support_url' => config('lunar.panel.support_url'),
                'media_max_kb' => (int) config('lunar.media.max_upload_kb', 8192),
            ],
            'locale' => fn () => app()->getLocale(),
            'availableLocales' => fn () => $this->manager->availableLocales(),
            'navigation' => fn () => $this->manager->navigation()->toArray($user),
            'settingsNavigation' => fn () => $this->manager->settingsNavigation()->toArray($user, skipMenus: true),
            'pageId' => fn () => $this->currentPagePrefix($request),
            'slots' => fn () => $this->manager->slots()->forPage($this->currentPagePrefix($request), $user),
            'pageActions' => fn () => $this->manager
                ->resolvePageActions($this->currentPagePrefix($request))
                ->resolve($this->currentRecord($request)),
            // The palette needs its quick actions before the first keystroke,
            // so they ship with the page rather than behind the search fetch.
            'searchCommands' => fn () => $this->manager->resolveSearchCommands()->resolve(),
            'searchSources' => fn () => $this->manager->resolveSearchSources()->kinds(),
            // The record this page is showing, shaped as a search row, so the
            // palette can offer it back under "Recently viewed".
            'visitedRecord' => fn () => ($record = $this->currentRecord($request))
                ? $this->manager->resolveSearchSources()->rowFor($record)
                : null,
            // Prefixed current values of every form slice on the page's
            // record, so the page's form can seed them without each edit
            // controller knowing which slices apply.
            'formSliceValues' => fn () => $this->formSliceValues($request),
        ]);
    }

    /**
     * Slice values for the page's form target: the deepest route-bound
     * model, the same record EditDraftController drafts (a product's variant
     * on the variant page, not the product). Empty when nothing is bound or
     * the model has no slices; create pages, which bind nothing, pass their
     * own for a fresh instance.
     *
     * @return array<string, mixed>|object
     */
    protected function formSliceValues(Request $request): array|object
    {
        $record = collect($request->route()?->parameters() ?? [])
            ->last(fn (mixed $parameter): bool => $parameter instanceof Model);

        return $record ? ($this->formSlices->values($record) ?: (object) []) : (object) [];
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

    /** @return array{id: mixed, name: string, email: string|null, avatar: string|null, admin: bool} */
    protected function serializeUser(Authenticatable $user): array
    {
        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return [
            'id' => $user->getAuthIdentifier(),
            'name' => $name !== '' ? $name : (string) ($user->email ?? ''),
            'email' => $user->email ?? null,
            'avatar' => Gravatar::url($user->email ?? null),
            'admin' => (bool) ($user->admin ?? false),
        ];
    }
}
