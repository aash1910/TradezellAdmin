<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendOtpNotification extends Notification
{
    use Queueable;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your OTP Code')
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('Your One-Time Password (OTP) is: **' . $this->otp . '**')
            ->line('This OTP will expire in 1 minutes.')
            ->line('If you did not request this, please ignore this email.');
    }
}
