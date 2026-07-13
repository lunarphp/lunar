<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Models\Channel;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;

class ChannelIndexController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [
        ['key' => 'handle', 'label' => 'Handle', 'width' => 'minmax(0, 0.8fr)'],
        ['key' => 'name', 'label' => 'Name', 'width' => 'minmax(0, 1.2fr)'],
        ['key' => 'url', 'label' => 'URL', 'width' => 'minmax(0, 1.4fr)'],
        ['key' => 'status', 'label' => 'Status', 'width' => '110px'],
    ];

    public function index(): Response
    {
        $resolver = $this->resolveTable('channels.index');

        $channels = Channel::orderBy('name')->get()->map(fn (Channel $channel): array => [
            'id' => $channel->id,
            'name' => $channel->name,
            'handle' => $channel->handle,
            'url' => $channel->url,
            'default' => $channel->default,
            'status' => $channel->status ? (string) $channel->status : null,
            'urls' => [
                'edit' => route('panel.settings.channels.edit', $channel),
            ],
            '_actions' => $resolver->resolveRowActionUrls($channel),
        ]);

        return Inertia::render('settings/channels/Index', [
            'channels' => $channels,
            ...$this->tableProps($resolver, $this->columns),
            'urls' => [
                'store' => route('panel.settings.channels.store'),
            ],
        ]);
    }
}
