<?php

namespace GetCandy\LivewireTables\Components\Actions;

use Closure;
use GetCandy\LivewireTables\Components\Concerns\HasClosure;
use GetCandy\LivewireTables\Components\Concerns\HasLivewireComponent;
use GetCandy\LivewireTables\Components\Concerns\HasViewComponent;
use GetCandy\LivewireTables\Components\Concerns\HasViewProperties;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component;

class Action extends Component implements Htmlable
{
    use HasLivewireComponent,
        HasClosure,
        HasViewComponent,
        HasViewProperties;

    public $record = null;

    /**
     * The label for the action
     *
     * @var string|null
     */
    public $label = null;

    /**
     * The URL for the action.
     *
     * @var string|null
     */
    public ?Closure $url = null;

    /**
     * Set the label on the action.
     *
     * @param  string  $label
     * @return self
     */
    public function label($label): self
    {
        $this->label = $label;

        return $this;
    }

    public function record($record): self
    {
        $this->record = $record;

        return $this;
    }

    public function url(Closure $closure): self
    {
        $this->url = $closure;

        return $this;
    }

    public function toHtml()
    {
        return $this->render();
    }

    public function render()
    {
        return view('tables::actions.base', [
            'label' => $this->label,
            'url' => $this->url,
            'record' => $this->record,
        ]);
    }
}
