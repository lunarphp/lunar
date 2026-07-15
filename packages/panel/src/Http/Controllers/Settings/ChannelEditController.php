<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Channels\DeletesChannel;
use Lunar\Core\Contracts\Actions\Channels\UpdatesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;
use Lunar\Core\States\Channel\ChannelState;

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

    public function update(Request $request, Channel $channel, UpdatesChannel $updatesChannel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'default' => ['sometimes', 'boolean'],
            'status' => ['nullable', Rule::in(ChannelState::getStateMapping()->keys()->all())],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'default' => (bool) ($validated['default'] ?? false),
        ];

        if (($validated['status'] ?? null) !== null) {
            $attributes['status'] = $validated['status'];
        }

        $updatesChannel->execute($channel, $attributes);

        return redirect()->route('panel.settings.channels.index')->with('success', __('panel::channels.flash_updated'));
    }

    public function destroy(Channel $channel, DeletesChannel $deletesChannel): RedirectResponse
    {
        try {
            $deletesChannel->execute($channel);
        } catch (ChannelActionException) {
            return back()->with('error', __('panel::channels.delete_blocked'));
        }

        return redirect()->route('panel.settings.channels.index')->with('success', __('panel::channels.flash_deleted'));
    }
}
