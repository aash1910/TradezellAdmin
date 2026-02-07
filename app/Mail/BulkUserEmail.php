<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkUserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $body
     * @param User $user
     */
    public function __construct(string $subject, string $body, User $user)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.bulk-user');
    }
}
