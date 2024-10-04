<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationVerificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function build() {
        return $this->markdown('emails.registration.verification')
            ->subject('Thi is a test subject')
            ->with([
                'user' => 'This is a test user',
            ]);
    }

}
