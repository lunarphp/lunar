<?php

namespace GetCandy\Hub\Http\Livewire;

use Livewire\Component;

class PageComponent extends Component
{
    protected static string $view;

    protected static string $navigationLabel;

    protected static string $navigationIcon;

    protected static string $navigationGroup;

    protected static array $params = [];

    public static function setTitle(string $title)
    {
        self::$params['title'] = $title ?? 'Page';
    }

    /**
     * @param  array  $params
     */
    public static function setParams(array $params): void
    {
        self::$params = array_merge(self::$params, $params);
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.'.static::$view)
            ->layout('adminhub::layouts.app', static::$params);
    }
}
