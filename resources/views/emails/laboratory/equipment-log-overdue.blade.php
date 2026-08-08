@php
    $personnelName = trim(collect([
        $log->personnel?->fname,
        $log->personnel?->mname,
        $log->personnel?->lname,
        $log->personnel?->suffix,
    ])->filter()->implode(' '));
    $equipmentName = $log->equipment?->name ?: 'Equipment';
    $typeLabel = $equipmentType === 'ict' ? 'ICT equipment' : 'laboratory equipment';
@endphp
@component('mail::message')
# Equipment Return Reminder

Hello {{ $personnelName !== '' ? $personnelName : 'there' }},

This is a quick reminder that your reserved time for the **{{ $equipmentName }}** has ended.

@component('mail::table')
| | |
| ------------- |:-------------|
| **Started At** | {{ optional($log->started_at)->format('F j, Y g:i A') ?? 'N/A' }} |
| **Expected End** | {{ optional($log->end_use_at)->format('F j, Y g:i A') ?? 'N/A' }} |
@endcomponent

Please return the equipment or kindly update your reservation if you still need to use it.

@if (!empty($equipmentUrl))
@component('mail::button', ['url' => $equipmentUrl])
Manage Reservation
@endcomponent
@endif

If you have already returned the equipment, please disregard this notice.

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent