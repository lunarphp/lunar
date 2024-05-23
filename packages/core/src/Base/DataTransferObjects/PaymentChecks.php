<?php

namespace Lunar\Base\DataTransferObjects;

class PaymentChecks implements \ArrayAccess, \Iterator
{
    private int $position = 0;

    protected array $checks = [];

    public function addCheck(PaymentCheck $check): void
    {
        $this->checks[] = $check;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function current(): PaymentCheck
    {
        return $this->checks[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->checks[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->checks[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->checks[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->checks[] = $value;
        } else {
            $this->checks[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->checks[$offset]);
    }
}
