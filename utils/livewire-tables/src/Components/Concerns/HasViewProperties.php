<?php

namespace Lunar\LivewireTables\Components\Concerns;

use Closure;

trait HasViewProperties
{
    public $field = null;

    public $heading = null;

    public function field($field)
    {
        $this->field = $field;

        return $this;
    }

    public function heading($heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function getHeading()
    {
        if ($this->heading === false) {
            return null;
        }

        return $this->heading ?: $this->translate($this->field, 'headings');
    }

    public static function make($field, Closure $value = null)
    {
        $class = app(static::class)->field($field);

        if ($value) {
            $class->value($value);
        }

        return $class;
    }
}
