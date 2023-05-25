<?php

namespace Lunar\Hub\Menu;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MenuRegistry
{
    /**
     * The slots which are currently registered.
     */
    protected Collection $slots;

    /**
     * Initialise the class.
     */
    public function __construct()
    {
        $this->slots = collect([
            new MenuSlot('sidebar'),
        ]);
    }

    /**
     * Getter/Setter for the requested slot. If the slot does not exist
     * then a new one will be added to the slots property and returned.
     *
     * @param  string  $handle
     * @return \Lunar\Hub\Menu\MenuSlot
     */
    public function slot($handle): MenuSlot
    {
        $handle = Str::slug($handle);

        $slot = $this->slots->first(function (MenuSlot $slot) use ($handle) {
            return $slot->getHandle() == $handle;
        });

        if ($slot) {
            return $slot;
        }

        $slot = new MenuSlot($handle);
        $this->slots->push($slot);

        return $slot;
    }
}
