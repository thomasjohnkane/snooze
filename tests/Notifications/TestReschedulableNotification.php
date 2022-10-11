<?php

namespace Thomasjohnkane\Snooze\Tests\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Thomasjohnkane\Snooze\Tests\Models\User;

class TestReschedulableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var User */
    public $newUser;

    /**
     * @param  User  $newUser
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User')
            ->line(sprintf('Email: %s', $this->newUser->email));
    }

    public function shouldRescheduleFor(object $notifiable)
    {
        if($notifiable->id === 1){
            return now()->addMinutes(5);
        }

        return null;
    }
}
