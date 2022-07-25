<?php

namespace GetCandy\Hub;

use GetCandy\Hub\Assets\Script;
use GetCandy\Hub\Assets\Style;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GetCandyHub
{
    /**
     * Registered scripts.
     *
     * @var array<string, \GetCandy\Hub\Assets\Script>
     */
    public static $scripts = [];

    /**
     * Registered styles.
     *
     * @var array<string, \GetCandy\Hub\Assets\Style>
     */
    public static $styles = [];

    /**
     * Get scripts that should be registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, \GetCandy\Hub\Assets\Script>
     */
    public static function scripts(): array
    {
        return static::$scripts;
    }

    /**
     * Get styles that should be registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, \GetCandy\Hub\Assets\Script>
     */
    public static function styles(): array
    {
        return static::$styles;
    }

    /**
     * Register script with Getcandy.
     *
     * @param  string  $name
     * @param  string|Script  $path
     * @return static
     */
    public static function script(string|Script $name, string $path): static
    {
        static::$scripts[] = new Script($name, $path);

        return new static();
    }

    /**
     * Register remote script with Getcandy.
     *
     * @param  string  $path
     * @return static
     */
    public static function remoteScript($path): static
    {
        return static::script(Script::remote($path), $path);
    }

    /**
     * Register style with Getcandy.
     *
     * @param  string  $name
     * @param  string|Style  $path
     * @return static
     */
    public static function style(string|Style $name, string $path): static
    {
        static::$styles[] = new Style($name, $path);

        return new static();
    }

    /**
     * Register remote style with Getcandy.
     *
     * @param  string  $path
     * @return static
     */
    public static function remoteStyle($path): static
    {
        return static::style(Style::remote($path), $path);
    }

    public static function icon($icon, $attrs = null, $style = 'outline'): static
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

    public static function paymentIcons(): string
    {
        return File::get(
            __DIR__.'/../resources/icons/payment_icons.svg'
        );
    }
}
