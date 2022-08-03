<?php

namespace GetCandy\Hub\Tests\Stubs\Mailers;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestAMailer extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public $subject = 'Test A Mailer';

    public $bcc = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('test_a_mailer');
    }
}
