<?php

namespace GetCandy\Hub\Views\Components;

use Illuminate\View\Component;

class Gravatar extends Component
{
    /**
     * The email address for the gravatar.
     *
     * @var string
     */
    public $email;

    /**
     * Initialise the component.
     *
     * @param  string  $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Gets a hash of the email ready for display.
     *
     * @return string
     */
    protected function getEmailHash()
    {
        return md5(strtolower(
            trim($this->email)
        ));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.gravatar', [
            'hash' => $this->getEmailHash(),
        ]);
    }
}
