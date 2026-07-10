<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Contracts\Actions\Channels\CreatesChannel;
use Lunar\Core\States\Channel\ChannelState;

class ChannelCreateController
{
    public function store(Request $request, CreatesChannel $createsChannel): RedirectResponse
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

        $createsChannel->execute($attributes);

        return redirect()->route('panel.settings.channels.index')->with('success', 'Channel created.');
    }
}
