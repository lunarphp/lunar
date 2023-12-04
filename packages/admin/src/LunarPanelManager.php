<?php

namespace Lunar\Admin;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Lunar\Admin\Filament\AvatarProviders\GravatarProvider;
use Lunar\Admin\Support\Extending\BaseExtension;
use Lunar\Admin\Support\Extending\ResourceExtension;
use Lunar\Admin\Support\Facades\LunarAccessControl;

class LunarPanelManager
{
    protected ?\Closure $closure = null;

    protected array $extensions = [];

    protected string $panelId = 'lunar';

    public function register(): self
    {
        $panel = $this->defaultPanel();

        if ($this->closure instanceof \Closure) {
            $fn = $this->closure;
            $panel = $fn($panel);
        }

        $panel->id($this->panelId);

        Filament::registerPanel($panel);

        return $this;
    }

    public function panel(\Closure $closure): self
    {
        $this->closure = $closure;

        return $this;
    }

    public function getPanel(): Panel
    {
        return Filament::getPanel($this->panelId);
    }

    protected function defaultPanel(): Panel
    {
        $brandAsset = function ($asset) {
            $vendorPath = 'vendor/lunarpanel/';

            if (file_exists(public_path($vendorPath.$asset))) {
                return asset($vendorPath.$asset);
            } else {
                $type = str($asset)
                    ->endsWith('.png') ? 'image/png' : 'image/svg+xml';

                return "data:{$type};base64,".base64_encode(file_get_contents(__DIR__.'/../public/'.$asset));
            }
        };

        return Panel::make()
            ->default()
            ->id($this->panelId)
            ->brandName('Lunar')
            ->brandLogo($brandAsset('lunar-logo.svg'))
            ->darkModeBrandLogo($brandAsset('lunar-logo-dark.svg'))
            ->favicon($brandAsset('lunar-icon.png'))
            ->brandLogoHeight('2rem')
            ->path('lunar')
            ->authGuard('staff')
            ->defaultAvatarProvider(GravatarProvider::class)
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->font('Poppins')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->plugin(LunarPlugin::make());
    }

    public function registerExtension(BaseExtension|ResourceExtension $extension, string $pageClass): self
    {
        $this->extensions[$pageClass][] = $extension;

        return $this;
    }

    public function useRoleAsAdmin(string|array $roleHandle): self
    {
        LunarAccessControl::useRoleAsAdmin($roleHandle);

        return $this;
    }

    public function callHook(string $class, string $hookName, ...$args): mixed
    {
        if (isset($this->extensions[$class])) {
            foreach ($this->extensions[$class] as $extension) {
                $args[0] = $extension->{$hookName}(...$args);
            }
        }

        return $args[0];
    }
}
