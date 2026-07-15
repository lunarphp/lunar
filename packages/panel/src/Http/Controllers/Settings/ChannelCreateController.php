<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Channels\CreatesChannel;
use Lunar\Panel\Http\Requests\Settings\ChannelRequest;

class ChannelCreateController
{
    public function store(ChannelRequest $request, CreatesChannel $createsChannel): RedirectResponse
    {
        $createsChannel->execute($request->channelAttributes());

        return redirect()->route('panel.settings.channels.index')->with('success', __('panel::channels.flash_created'));
    }
}
