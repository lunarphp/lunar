<?php

namespace GetCandy\LivewireTables\Components\Columns;

use Closure;
use GetCandy\LivewireTables\TableManifest;
use Livewire\Component;

class AvatarColumn extends BaseColumn
{
    /**
     * Whether this should use gravatar
     *
     * @var bool
     */
    public bool $gravatar = false;

    public function gravatar($state = true)
    {
        $this->gravatar = $state;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $value = $this->getValue();

        if ($this->gravatar) {
            $hash = md5(strtolower(
                trim($value)
            ));

            $value = "https://www.gravatar.com/avatar/{$hash}?s=100&d=mp";
        }

        return view('tables::columns.avatar', [
            'record' => $this->record,
            'value' => $value,
            'gravatar' => $this->gravatar,
        ]);
    }
}
