<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Channel;
use Lunar\Core\States\Channel\ChannelState;

/** Shared by the channel store and update endpoints, whose rules are identical. */
class ChannelRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Channel|null $channel */
        $channel = $this->route('channel');

        return [
            'name' => [
                'required', 'string', 'max:255',
                // The unique handle is derived by slugging the name, so a
                // colliding name would hit the DB constraint — reject it here.
                function (string $attribute, mixed $value, Closure $fail) use ($channel): void {
                    $taken = Channel::query()
                        ->where('handle', Str::slug((string) $value))
                        ->when($channel, fn ($query) => $query->whereKeyNot($channel->getKey()))
                        ->exists();

                    if ($taken) {
                        $fail('validation.unique')->translate();
                    }
                },
            ],
            'url' => ['nullable', 'url', 'max:255'],
            'default' => ['sometimes', 'boolean'],
            'status' => ['nullable', Rule::in(ChannelState::getStateMapping()->keys()->all())],
        ];
    }

    /**
     * The validated input shaped for the channel actions: url normalised to
     * null, and the default flag and status omitted entirely when not
     * supplied so an update leaves them untouched.
     *
     * @return array<string, mixed>
     */
    public function channelAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'name' => $validated['name'],
            'url' => $validated['url'] ?? null,
        ];

        if (array_key_exists('default', $validated)) {
            $attributes['default'] = (bool) $validated['default'];
        }

        if (($validated['status'] ?? null) !== null) {
            $attributes['status'] = $validated['status'];
        }

        return $attributes;
    }
}
