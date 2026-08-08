@php
    $displayName = trim((string) ($recipientName ?? 'Participant'));
    $displayEventTitle = trim((string) ($eventTitle ?? 'this event'));
@endphp
@component('mail::message')
# Your Certificate is Ready!

Dear **{{ $displayName }}**,

Thank you for joining us for **{{ $displayEventTitle }}**. We are thrilled to have had you!

@if (!empty($eventDate))
**Event Date:** {{ $eventDate }}
@endif

Congratulations on completing the activity. We have attached your official certificate of participation to this email for your records. 

We appreciate your time and look forward to seeing you at our future events!

@component('mail::panel')
**Stay Connected with DA-CBC**
- [Official Website](https://dacbc.philrice.gov.ph/)
- [Facebook](https://www.facebook.com/DACropBiotechCenter)
- [Email](mailto:cropbiotechcenter@gmail.com)
@endcomponent

Best regards,<br>
DA-Crop Biotechnology Center

<div style="font-size: 10px; color: #9ca3af; margin-top: 20px;">Reference ID: {{ $eventId }}</div>
@endcomponent