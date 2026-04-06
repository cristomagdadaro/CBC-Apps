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

<p>Dear {{ $personnelName !== '' ? $personnelName : 'User' }},</p>

<p>Your {{ $typeLabel }} usage session for <strong>{{ $equipmentName }}</strong> is now overdue.</p>

<p>
    Started at: {{ optional($log->started_at)->format('F j, Y g:i A') ?? 'N/A' }}<br>
    Expected end: {{ optional($log->end_use_at)->format('F j, Y g:i A') ?? 'N/A' }}
</p>

<p>Please return the equipment or update the expected end of use as soon as possible.</p>

<p>OneCBC Portal</p>
