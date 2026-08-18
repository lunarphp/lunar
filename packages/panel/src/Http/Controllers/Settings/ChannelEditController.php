<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Channels\DeletesChannel;
use Lunar\Core\Contracts\Actions\Channels\UpdatesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;
use Lunar\Panel\Http\Requests\Settings\ChannelRequest;

class ChannelEditController
{
    public function edit(Channel $channel): Response
    {
        return Inertia::render('settings/channels/Edit', [
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'handle' => $channel->handle,
                'url' => $channel->url,
                'default' => $channel->default,
                'status' => $channel->status ? (string) $channel->status : null,
            ],
            'hasOrderHistory' => $channel->hasOrderHistory(),
            'urls' => [
                'update' => route('panel.settings.channels.update', $channel),
                'destroy' => route('panel.settings.channels.destroy', $channel),
                'index' => route('panel.settings.channels.index'),
            ],
        ]);
    }

    public function update(ChannelRequest $request, Channel $channel, UpdatesChannel $updatesChannel): RedirectResponse
    {
        try {
            $updatesChannel->execute($channel, $request->channelAttributes());
        } catch (ChannelActionException) {
            return back()->with('error', __('panel::channels.default_unset_blocked'));
        }

        return redirect()->route('panel.settings.channels.index')->with('success', __('panel::channels.flash_updated'));
    }

    public function destroy(Channel $channel, DeletesChannel $deletesChannel): RedirectResponse
    {
        try {
            $deletesChannel->execute($channel);
        } catch (ChannelActionException) {
            return back()->with('error', $channel->default
                ? __('panel::channels.delete_blocked_default')
                : __('panel::channels.delete_blocked'));
        }

        return redirect()->route('panel.settings.channels.index')->with('success', __('panel::channels.flash_deleted'));
    }
}
