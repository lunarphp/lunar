<?php

namespace Lunar\Hub;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Lunar\Hub\Assets\Script;
use Lunar\Hub\Assets\Style;

class LunarHub
{
    /**
     * Registered scripts.
     *
     * @var array<string, \Lunar\Hub\Assets\Script>
     */
    public static $scripts = [];

    /**
     * Registered styles.
     *
     * @var array<string, \Lunar\Hub\Assets\Style>
     */
    public static $styles = [];

    /**
     * Get scripts that should be registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, \Lunar\Hub\Assets\Script>
     */
    public static function scripts(): array
    {
        return static::$scripts;
    }

    /**
     * Get styles that should be registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, \Lunar\Hub\Assets\Script>
     */
    public static function styles(): array
    {
        return static::$styles;
    }

    /**
     * Register script with Lunar.
     */
    public static function script(string|Script $name, string $path): static
    {
        static::$scripts[] = new Script($name, $path);

        return new static();
    }

    /**
     * Register remote script with Lunar.
     *
     * @param  string  $path
     */
    public static function remoteScript($path): static
    {
        return static::script(Script::remote($path), $path);
    }

    /**
     * Register style with Lunar.
     */
    public static function style(string|Style $name, string $path): static
    {
        static::$styles[] = new Style($name, $path);

        return new static();
    }

    /**
     * Register remote style with Lunar.
     *
     * @param  string  $path
     */
    public static function remoteStyle($path): static
    {
        return static::style(Style::remote($path), $path);
    }

    public static function icon($icon, $attrs = null, $style = 'outline')
    {
        if ($attrs) {
            $attrs = " class=\"{$attrs}\"";
        }

        if (Str::startsWith($icon, '<svg')) {
            return str_replace('<svg', sprintf('<svg%s', $attrs), $icon);
        }

        $iconPath = __DIR__."/../resources/icons/{$style}/$icon.svg";

        if (! File::exists($iconPath)) {
            return;
        }

        return str_replace('<svg', sprintf('<svg%s', $attrs), File::get($iconPath));
    }

    public static function paymentIcons()
    {
        return File::get(
            __DIR__.'/../resources/icons/payment_icons.svg'
        );
    }
}
