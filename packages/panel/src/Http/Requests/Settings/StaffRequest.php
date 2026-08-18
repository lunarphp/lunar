<?php

namespace Lunar\Panel\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lunar\Core\Models\Staff;
use Lunar\Core\Support\Facades\LunarAccessControl;

/**
 * Shared by the staff store and update endpoints. The password is required
 * when creating; on update a blank password means "leave it unchanged".
 */
class StaffRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Staff|null $staff */
        $staff = $this->route('staff');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique(Staff::class, 'email')->ignore($staff?->id)->withoutTrashed(),
            ],
            'password' => [$staff ? 'nullable' : 'required', Password::defaults()],
            'admin' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => [Rule::in(LunarAccessControl::getRoles()->pluck('handle')->all())],
        ];
    }

    /**
     * The validated input shaped for the staff actions: the admin flag cast,
     * and optional keys omitted when absent so an update leaves them
     * untouched.
     *
     * @return array<string, mixed>
     */
    public function staffAttributes(): array
    {
        $validated = $this->validated();

        $attributes = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
        ];

        if (($validated['password'] ?? null) !== null) {
            $attributes['password'] = $validated['password'];
        }

        if (array_key_exists('admin', $validated)) {
            $attributes['admin'] = (bool) $validated['admin'];
        }

        if (array_key_exists('roles', $validated)) {
            $attributes['roles'] = $validated['roles'];
        }

        return $attributes;
    }
}
