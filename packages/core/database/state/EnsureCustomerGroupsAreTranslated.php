<?php

namespace GetCandy\Database\State;

use GetCandy\FieldTypes\TranslatedText;
use GetCandy\Models\CustomerGroup;

class EnsureCustomerGroupsAreTranslated
{
    public function run(): void
    {
        if (! $this->canRun() || ! $this->shouldRun()) {
            return;
        }

        CustomerGroup::all()->each(
            fn (CustomerGroup $group) => $group->update([
                'name' => new TranslatedText(collect([
                    'en' => $group->name,
                ])),
            ])
        );
    }

    protected function canRun(): bool
    {
        return CustomerGroup::count();
    }

    protected function shouldRun(): bool
    {
        return CustomerGroup::all()->filter(
            fn ($group) => is_string($group->name)
        )->count() > 0;
    }
}
