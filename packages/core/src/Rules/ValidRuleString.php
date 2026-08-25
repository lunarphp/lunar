<?php

namespace Lunar\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Accepts only strings Laravel's validator can execute as a rule. Unknown
 * rules, missing parameters, and malformed patterns only throw once
 * validation runs, so a bad entry stored on an attribute would 500 every
 * save of a record using that attribute (spec 0062). A dry run against a
 * probe value surfaces the problem while the rule is being authored instead.
 */
class ValidRuleString implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail(__('lunar::validation.unusable_rule', ['rule' => json_encode($value)]));

            return;
        }

        try {
            // Only executability matters; the probe outcome is discarded.
            Validator::make(['probe' => 'probe'], ['probe' => [$value]])->fails();
        } catch (Throwable) {
            $fail(__('lunar::validation.unusable_rule', ['rule' => $value]));
        }
    }
}
