<?php

namespace Lunar\LivewireTables\Components\Columns;

use Closure;

class ProgressColumn extends BaseColumn
{
    protected string|Closure $color = 'primary';

    protected string|Closure|null $label = null;

    protected ?Closure $progress = null;

    public function color(string|Closure $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getColor(): string
    {
        return !is_string($this->color)
            ? call_user_func($this->color, $this->record)
            : $this->color;
    }

    public function progress(Closure $progress): static
    {
        $this->progress = $progress;
        return $this;
    }

    public function getProgress(): int|float
    {
        if ($this->progress === null) {
            return floor($this->getValue());
        }
        return (float)call_user_func($this->progress, $this->record);
    }

    public function label(string|Closure $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): ?string
    {
        return is_callable($this->label)
            ? call_user_func($this->label, $this->record)
            : $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::columns.progress', [
            'record' => $this->record,
            'color' => $this->getColor(),
            'progress' => $this->getProgress(),
            'label' => $this->getLabel(),
        ]);
    }
}
