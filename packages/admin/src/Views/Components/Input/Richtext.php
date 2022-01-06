<?php

namespace GetCandy\Hub\Views\Components\Input;

use Illuminate\View\Component;

class Richtext extends Component
{
    /**
     * The initial value.
     *
     * @var string
     */
    public $initialValue = null;

    /**
     * Instantiate the component.
     *
     * @param  string  $initialValue
     */
    public function __construct($initialValue = null)
    {
        $this->initialValue = $initialValue;
    }

    /**
     * Get a unique instance id.
     *
     * @return string
     */
    protected function getInstanceId()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 25; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.input.richtext', [
            'instanceId' => $this->getInstanceId(),
        ]);
    }
}
