<?php

namespace GetCandy\Hub\Slots;

interface AbstractSlot
{
    /**
     * Get the handle of the slot.
     *
     * @return string
     */
    public function getSlotHandle();

    /**
     * Get the position in the view where the slot appears.
     *
     * @return string
     */
    public function getSlotPosition();

    /**
     * Get the title of the slot.
     *
     * @return string
     */
    public function getSlotTitle();
}
