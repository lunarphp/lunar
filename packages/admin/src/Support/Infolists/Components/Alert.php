<?php

namespace Lunar\Admin\Support\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Concerns\HasColor;
use Filament\Infolists\Components\Concerns\HasIcon;
use Filament\Infolists\Components\Entry;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;

class Alert extends Entry
{
    use HasColor{
        getColor as baseGetColor;
    }
    use HasIcon;

    protected string $view = 'lunarpanel::infolists.components.alert';

    protected string|Htmlable|Closure|null $content = null;

    protected ?Closure $configureColorUsing = null;

    protected string $defaultIcon = 'heroicon-o-information-circle';

    public function content(string|Htmlable|Closure|null $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getColor(mixed $state): string|array|null
    {
        if (filled($this->configureColorUsing)) {
            $this->evaluate($this->configureColorUsing);
        }

        return $this->baseGetColor($state);
    }

    /**
     * @return  string | Htmlable | null
     */
    public function getContent()
    {
        return $this->evaluate($this->content);
    }

    public function configureColor($configureColorUsing = null): static
    {
        $this->configureColorUsing = $configureColorUsing;

        return $this;
    }

    public function danger(): static
    {
        $this->color('danger');

        return $this;
    }

    public function info(): static
    {
        $this->color(Color::Sky);

        return $this;
    }

    public function success(): static
    {
        $this->color('success');

        return $this;
    }

    public function warning(): static
    {
        $this->color(Color::Amber);

        return $this;
    }

    public function defaultIcon(string $icon): static
    {
        $this->defaultIcon = $icon;

        return $this;
    }

    public function getDefaultIcon(): string
    {
        return $this->defaultIcon;
    }
}
