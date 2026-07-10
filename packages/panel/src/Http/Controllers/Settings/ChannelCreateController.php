<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Channel;
use Lunar\Core\States\Channel\ChannelState;

class ChannelCreateController
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'default' => ['sometimes', 'boolean'],
            'status' => ['nullable', Rule::in(ChannelState::getStateMapping()->keys()->all())],
        ]);

        $default = (bool) ($validated['default'] ?? false);

        if ($default) {
            Channel::where('default', true)->update(['default' => false]);
        }

        Channel::create([
            'name' => $validated['name'],
            'handle' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'default' => $default,
            'status' => $validated['status'] ?? null,
        ]);

        return redirect()->route('panel.settings.channels.index')->with('success', 'Channel created.');
    }
}
