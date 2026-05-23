<?php

namespace Lunar\Filament\Actions\Concerns;

use Closure;
use Filament\Forms\Components\Toggle;

/**
 * Shared "type CONFIRM to proceed" / explicit-toggle pattern for destructive
 * actions. Mixed into Filament actions whose `action()` callback acts on
 * real data (refund, capture, unpublish, archive, destructive bulk verbs).
 */
trait ConfirmsDestructiveAction
{
    /**
     * Confirmation toggle that must be enabled before the action submits.
     * Pair with a `helperText`/label per-action by overriding the returned
     * input fluently.
     */
    protected function confirmToggle(?string $helperText = null): Toggle
    {
        return Toggle::make('confirm')
            ->label(__('lunar-filament::actions.shared.confirm.label'))
            ->helperText($helperText)
            ->rules([
                fn () => function (string $attribute, $value, Closure $fail): void {
                    if ($value !== true) {
                        $fail(__('lunar-filament::actions.shared.confirm.error'));
                    }
                },
            ]);
    }
}
