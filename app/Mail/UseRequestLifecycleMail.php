<?php

namespace App\Mail;

use App\Models\RequestFormPivot;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UseRequestLifecycleMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public readonly string $requestUrl;
    public readonly string $eventLabel;

    public function __construct(
        public readonly RequestFormPivot $request,
        public readonly string $event,
    ) {
        $this->requestUrl = route('labReq.guest.index', ['request' => $request->id]);
        $this->eventLabel = match ($event) {
            RequestFormPivot::STATUS_APPROVED => 'approved',
            RequestFormPivot::STATUS_RELEASED => 'released',
            RequestFormPivot::STATUS_RETURNED => 'returned',
            RequestFormPivot::STATUS_REJECTED => 'rejected',
            'overdue' => 'overdue',
            default => 'updated',
        };
    }

    public function build(): self
    {
        $subjectPrefix = match ($this->event) {
            RequestFormPivot::STATUS_APPROVED => 'FES request approved',
            RequestFormPivot::STATUS_RELEASED => 'FES request released',
            RequestFormPivot::STATUS_RETURNED => 'FES request returned',
            RequestFormPivot::STATUS_REJECTED => 'FES request rejected',
            'overdue' => 'FES request overdue',
            default => 'FES request updated',
        };

        return $this->subject("{$subjectPrefix}: {$this->request->id}")
            ->view('emails.lab-request.lifecycle');
    }
}
