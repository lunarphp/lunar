<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Channel;
use Lunar\Core\States\Channel\ChannelState;
use Lunar\Panel\Http\Requests\Concerns\ValidatesFormSlices;

/** Shared by the channel store and update endpoints, whose rules are identical bar the name-to-handle collision check, which only applies on create. */
class ChannelRequest extends FormRequest
{
    use ValidatesFormSlices;

    /** @var class-string<Channel> */
    protected string $sliceModel = Channel::class;

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
                // On create the unique handle is derived by slugging the name,
                // so a colliding name would hit the DB constraint — reject it
                // here. An update keeps the existing handle, so any name goes.
                function (string $attribute, mixed $value, Closure $fail) use ($channel): void {
                    if ($channel) {
                        return;
                    }

                    $taken = Channel::query()
                        ->where('handle', Str::slug((string) $value))
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
