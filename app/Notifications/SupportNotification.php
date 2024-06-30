<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class SupportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $user, public $support)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['slack'];
    }

    public function toSlack()
    {

        $message = '*New Support Request From '.env('APP_ENV')." Environment * \n".
        '*Name* : '.$this->support->name."\n".
        '*Store Name* : '.$this->user->name."\n".
        '*Email* : '.$this->support->email."\n".
        '*Message* : '.$this->support->message;

        return (new SlackMessage)->content($message);
    }
}
