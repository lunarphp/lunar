<?php

namespace Lunar\Panel\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Staff;

class TwoFactorEmailCode extends Notification
{
    public function __construct(public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Staff $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('panel::auth.two_factor_email_subject'))
            ->line(__('panel::auth.two_factor_email_line1'))
            ->line('**'.$this->code.'**')
            ->line(__('panel::auth.two_factor_email_line2', ['count' => 10]));
    }
}
