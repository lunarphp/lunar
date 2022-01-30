<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

trait Notifies
{
    /**
     * Queues up a notification in either the browser or via a redirect.
     *
     * @param  string  $message
     * @param  string  $route
     * @return void|\Illuminate\Support\Facades\Redirect
     */
    public function notify($message, $route = null, $routeParams = [], $level = 'success')
    {
        if ($route) {
            session()->flash('notify.message', $message);
            session()->flash('notify.level', $level);

            return redirect()->route($route, $routeParams);
        }

        $this->dispatchBrowserEvent('notify', [
            'message' => $message,
            'level'   => $level,
        ]);
    }
}
