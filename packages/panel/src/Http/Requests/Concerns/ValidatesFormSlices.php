<?php

namespace Lunar\Panel\Http\Requests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lunar\Panel\Forms\FormSlices;

/**
 * Merges the rules of every form slice registered on `$sliceModel` into a
 * plain form request's own, so an add-on's fields validate with the form's
 * and surface in the same round. Update requests validate against the
 * route-bound record; store requests against a fresh instance. The slice
 * keys are kept out of validated(), so a request that hands its validated
 * input to an action wholesale never leaks them; sliceInput() carries them
 * to FormSlices::save().
 *
 * @property class-string<Model> $sliceModel
 */
trait ValidatesFormSlices
{
    /** @return array<string, mixed> */
    protected function validationRules(): array
    {
        return [
            ...parent::validationRules(),
            ...$this->formSlices()->rules($this->sliceModel, $this->sliceRecord()),
        ];
    }

    /**
     * The validated input without the slice keys.
     *
     * @param  array<int, string>|int|string|null  $key
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $this->formSlices()->set($this->sliceModel)->partition($validated)['rest'];
    }

    /**
     * The validated slice keys only, for FormSlices::save().
     *
     * @return array<string, mixed>
     */
    public function sliceInput(): array
    {
        return array_diff_key(parent::validated(), $this->validated());
    }

    protected function sliceRecord(): ?Model
    {
        $record = collect($this->route()?->parameters() ?? [])
            ->last(fn (mixed $parameter): bool => $parameter instanceof $this->sliceModel);

        return $record instanceof Model ? $record : null;
    }

    protected function formSlices(): FormSlices
    {
        return $this->container->make(FormSlices::class);
    }
}
