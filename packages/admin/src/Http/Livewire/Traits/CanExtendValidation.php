<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use Closure;
use Illuminate\Support\Facades\Log;

trait CanExtendValidation
{

    public static array $extendedValidationRules = [];

    public static array $extendedValidationMessages = [];

    /**
     * Extend validation rules
     *
     * @return void
     */
    public static function extendValidation(array $rules, array $messages = []): void
    {
        self::$extendedValidationRules = $rules;
        self::$extendedValidationMessages = $messages;
    }

    /**
     * Get extended validation rules
     *
     * @return array
     */
    protected function getExtendedValidationRules($parameters): array
    {
        return collect(self::$extendedValidationRules)
            ->map(function ($rules) use ($parameters) {

                if (is_array($rules) || $rules instanceof Closure) {
                    if ($rules instanceof Closure) $rules = [$rules];

                    return collect($rules)->map(function ($rule) use ($parameters) {
                        if ($rule instanceof Closure) {
                            return app()->call($rule, $parameters);
                        }

                        return $rule;
                    })->toArray();
                }

                return $rules;
            })->toArray();
    }

    /**
     * Get extended validation messages
     *
     * @return array
     */
    protected function getExtendedValidationMessages(): array
    {
        return self::$extendedValidationMessages;
    }
}
