<?php

namespace App\Mail;

use App\Models\PersonnelRegistration;
use App\Services\Personnel\PersonnelIdCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PersonnelRegistrationApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly PersonnelRegistration $registration) {}

    public function build(): self
    {
        $registration = $this->registration->loadMissing('personnel');
        $idCardService = app(PersonnelIdCardService::class);
        $card = $idCardService->cardData($registration);

        return $this->subject('Your OneCBC Personnel Registration is Approved')
            ->markdown('emails.personnel.registration-approved', [
                'registration' => $registration,
                'card' => $card,
            ])
            ->attachData(
                $idCardService->pdfBinary($registration),
                $idCardService->filename($registration),
                ['mime' => 'application/pdf'],
            );
    }
}
