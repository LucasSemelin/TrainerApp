<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WelcomeClientMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public bool $resetSent)
    {
        //
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome — set your password')
            ->view('emails.welcome_client');
    }
}
