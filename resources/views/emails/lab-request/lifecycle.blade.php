@php
    $requestForm = $request->request_form;
    $requester = $request->requester;
@endphp
@component('mail::message')
# Request Status Update

Hello {{ $requester?->name ?: 'there' }},

@if ($event === 'overdue')
This is a gentle reminder that the resources you borrowed are now past their scheduled return date.
@else
We are writing to inform you that your request is now **{{ strtoupper($eventLabel) }}**.
@endif

@component('mail::table')
| | |
| ------------- |:-------------|
| **Request ID** | {{ $request->id }} |
| **Purpose** | {{ $requestForm?->request_purpose ?: 'N/A' }} |
| **Date of Use** | {{ $requestForm?->date_of_use ?: 'N/A' }} @if ($requestForm?->date_of_use_end) to {{ $requestForm->date_of_use_end }} @endif |
| **Time of Use** | {{ $requestForm?->time_of_use ?: 'N/A' }} @if ($requestForm?->time_of_use_end) to {{ $requestForm->time_of_use_end }} @endif |
@endcomponent

@if ($request->approval_constraint)
**Special Conditions:** {{ $request->approval_constraint }}
@endif

@if ($request->disapproved_remarks)
**Remarks:** {{ $request->disapproved_remarks }}
@endif

@if ($event === 'overdue')
If you haven't returned these items yet, please coordinate with the FES officer as soon as possible.
@endif

@component('mail::button', ['url' => $requestUrl])
View Request Details
@endcomponent

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
