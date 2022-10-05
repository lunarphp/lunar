<?php

namespace Lunar\LivewireTables\Components\Columns;

use Closure;
use Illuminate\Support\Str;
use Livewire\Component;
use Lunar\LivewireTables\Components\Concerns\HasClosure;
use Lunar\LivewireTables\Components\Concerns\HasEloquentRelationships;
use Lunar\LivewireTables\Components\Concerns\HasLivewireComponent;
use Lunar\LivewireTables\Components\Concerns\HasTranslations;
use Lunar\LivewireTables\Components\Concerns\HasViewComponent;
use Lunar\LivewireTables\Components\Concerns\HasViewProperties;

abstract class BaseColumn extends Component
{
    use HasLivewireComponent,
        HasClosure,
        HasViewComponent,
        HasEloquentRelationships,
        HasTranslations,
        HasViewProperties;

    /**
     * The instance of the record from the table row.
     *
     * @var mixed
     */
    public $record;

    /**
     * The URL for the action.
     *
     * @var string|null
     */
    public ?Closure $url = null;

    /**
     * Whether the column is sortable.
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * The column this should appear after
     *
     * @var string
     */
    public $after = null;

    public function url(Closure $closure): self
    {
        $this->url = $closure;

        return $this;
    }

    public function after($column)
    {
        $this->after = $column;

        return $this;
    }

    /**
     * Set the property value for sortable.
     *
     * @param  bool  $sortable
     * @return self
     */
    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Set the field property.
     *
     * @param  string  $field
     * @return void
     */
    public function field($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Return whether the column is sortable.
     *
     * @return void
     */
    public function isSortable()
    {
        return (bool) $this->sortable;
    }

    /**
     * Return the column value.
     *
     * @return void
     */
    public function getValue()
    {
        if ($this->closure) {
            return call_user_func($this->closure, $this->record);
        }

        $relationName = $this->getRelationshipName();
        $relationColumn = $this->getRelationshipColumn();

        if ($relationName != $relationColumn) {
            return $this->record->{$relationName}?->{$relationColumn};
        }

        if (! $this->record) {
            return;
        }

        if (property_exists($this->record, $this->field)) {
            return $this->record->{$this->field};
        }

        return $this->record->getAttribute(
            $this->field
        );
    }

    /**
     * Set the record property.
     *
     * @param  mixed  $record
     * @return self
     */
    public function record($record): self
    {
        $this->record = $record;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('l-tables::columns.base', [
            'url' => $this->url,
            'record' => $this->record,
            'value' => $this->getValue(),
        ]);
    }
}
