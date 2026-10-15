@php
    $displayName = trim((string) ($recipientName ?? 'Participant'));
    $displayEventTitle = trim((string) ($eventTitle ?? 'this event'));
@endphp
@component('mail::message')
# Your Certificate is Ready!

Dear **{{ $displayName }}**,

Thank you for joining us for **{{ $displayEventTitle }}**. We were thrilled to have you with us!

Congratulations on completing the activity. We have attached your official certificate of participation to this email for your records.

@component('mail::table')
| | |
|:---|:---|
| **Recipient** | {{ $displayName }} |
| **Event** | {{ $displayEventTitle }} |
@if (!empty($eventDate))
| **Event Date** | {{ $eventDate }} |
@endif
@if (!empty($eventId))
| **Event ID** | {{ $eventId }} |
@endif
@endcomponent

We appreciate your time and look forward to seeing you at our future events!

@component('mail::panel')
**Stay Connected with DA-Crop Biotechnology Center**

[Official Website](https://dacbc.philrice.gov.ph/) &bull; [Facebook Page](https://www.facebook.com/DACropBiotechCenter) &bull; [Contact Email](mailto:cropbiotechcenter@gmail.com)
@endcomponent

Best regards,  
**DA-Crop Biotechnology Center**

@isset($eventId)
@component('mail::subcopy')
Reference ID: {{ $eventId }} &bull; This is an automated notification from OneCBC Portal.
@endcomponent
@endisset
@endcomponent
