<?php

namespace GetCandy\Hub;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GetCandyHub
{
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
            __DIR__."/../resources/icons/payment_icons.svg"
        );
    }
}
