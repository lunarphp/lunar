<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Channel;

class ChannelIndexController
{
    public function index(): Response
    {
        $channels = Channel::orderBy('name')->get()->map(fn (Channel $channel): array => [
            'id' => $channel->id,
            'name' => $channel->name,
            'handle' => $channel->handle,
            'url' => $channel->url,
            'default' => $channel->default,
            'status' => $channel->status ? (string) $channel->status : null,
            'urls' => [
                'edit' => route('panel.settings.channels.edit', $channel),
                'destroy' => route('panel.settings.channels.destroy', $channel),
            ],
        ]);

        return Inertia::render('settings/channels/Index', [
            'channels' => $channels,
            'urls' => [
                'store' => route('panel.settings.channels.store'),
            ],
        ]);
    }
}
