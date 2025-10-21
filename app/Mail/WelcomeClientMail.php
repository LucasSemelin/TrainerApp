<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
