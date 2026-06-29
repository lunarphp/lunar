<?php

namespace Lunar\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Channel;

class ChannelSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('lunar-filament::forms/selectors.channel.label'));
        $this->placeholder(__('lunar-filament::forms/selectors.channel.placeholder'));
        $this->relationship('channel', 'name');
        $this->preload();
    }

    /**
     * @return class-string<Model>
     */
    public function lunarModel(): string
    {
        return Channel::class;
    }
}
