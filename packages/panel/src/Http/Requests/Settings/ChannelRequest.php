<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Core\States\Channel\ChannelState;

/** Shared by the channel store and update endpoints, whose rules are identical. */
class ChannelRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'default' => ['sometimes', 'boolean'],
            'status' => ['nullable', Rule::in(ChannelState::getStateMapping()->keys()->all())],
        ];
    }

    /**
     * The validated input shaped for the channel actions: url normalised to
     * null, default cast, and status omitted entirely when not supplied.
     *
     * @return array<string, mixed>
     */
    public function channelAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
            'default' => (bool) ($validated['default'] ?? false),
        ];

        if (($validated['status'] ?? null) !== null) {
            $attributes['status'] = $validated['status'];
        }

        return $attributes;
    }
}
