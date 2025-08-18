<?php

namespace Lunar\Tests\Admin\Stubs\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class BuilderField implements FieldType, JsonSerializable
{
    protected $value;

    public function __construct($value = [])
    {
        $this->setValue($value);
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    public function getValue()
    {
        return json_decode($this->value ?? '[]', true);
    }

    public function setValue($value)
    {
        if (! is_array($value)) {
            throw new FieldTypeException(self::class.' value must be an array.');
        }

        $this->value = json_encode($this->normalize($value));
    }

    protected function normalize(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = $item['type'] ?? null;
            $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : [];

            // Remove null/empty values from data
            $data = array_filter($data, fn ($v) => ! is_null($v) && $v !== '');

            if (! is_null($type)) {
                $normalized[] = [
                    'type' => $type,
                    'data' => $data,
                ];
            }
        }

        return array_values($normalized);
    }

    public function getConfig(): array
    {
        return [
            'options' => [],
        ];
    }
}
