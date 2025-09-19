<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmailChangeVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $newEmail;
    public $verificationUrl;

    public function __construct(User $user, $newEmail, $verificationUrl)
    {
        $this->user = $user;
        $this->newEmail = $newEmail;
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        return $this->subject('Bestätige deine neue E-Mail-Adresse')
                    ->view('emails.EmailChangeVerificationMail');
    }
}