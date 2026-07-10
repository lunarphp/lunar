<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
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

    public function update(Request $request, Channel $channel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'default' => ['sometimes', 'boolean'],
            'status' => ['nullable', Rule::in(ChannelState::getStateMapping()->keys()->all())],
        ]);

        $default = (bool) ($validated['default'] ?? false);

        if ($default) {
            Channel::where('default', true)->where('id', '!=', $channel->id)->update(['default' => false]);
        }

        $channel->update([
            'name' => $validated['name'],
            'handle' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'default' => $default,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('panel.settings.channels.index')->with('success', 'Channel updated.');
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        if ($channel->hasOrderHistory()) {
            return back()->with('error', 'Cannot delete a channel with order history.');
        }

        $channel->delete();

        return redirect()->route('panel.settings.channels.index')->with('success', 'Channel deleted.');
    }
}
