<?php


namespace App\Mail;

use Illuminate\Mail\Mailable;


class VerificationCodeMail extends Mailable
{
    public $user;
    public $verificationCode;

    public function __construct($user, $verificationCode)
    {
        $this->user = $user;
        $this->verificationCode = $verificationCode;
    }

    public function build()
    {
        return $this->subject('Verification Code')
            ->view('emails.verification_code')
            ->with([
                'user' => $this->user,
                'verificationCode' => $this->verificationCode,
            ]);
    }
}
