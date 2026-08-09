<?php

namespace App\Mail;

use App\Models\PersonnelRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PersonnelRegistrationVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public readonly string $verificationUrl;

    public function __construct(public readonly PersonnelRegistration $registration)
    {
        $this->verificationUrl = URL::temporarySignedRoute(
            'personnel.registration.verify',
            now()->addDays(7),
            ['registration' => $registration->id],
        );
    }

    public function build(): self
    {
        return $this->subject('Verify your OneCBC personnel registration')
            ->markdown('emails.personnel.registration-verification');
    }
}
