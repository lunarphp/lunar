<?php

namespace GetCandy\Base;

interface FieldType
{
    public function getValue();

    public function setValue($value);
}
