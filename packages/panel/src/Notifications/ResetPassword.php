<?php

namespace Lunar\Panel\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Lunar\Core\Models\Staff;

class ResetPassword extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Staff $notifiable): MailMessage
    {
        $url = route('panel.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expire = config('auth.passwords.'.config('lunar.staff.provider', 'staff').'.expire', 60);

        return (new MailMessage)
            ->subject(__('panel::auth.reset_notification_subject'))
            ->line(__('panel::auth.reset_notification_line1'))
            ->action(__('panel::auth.reset_notification_action'), $url)
            ->line(__('panel::auth.reset_notification_line2', ['count' => $expire]));
    }
}
