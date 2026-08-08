@component('mail::message')
# New Event Form Submission

Hello,

A new response has just been submitted for an ongoing event. Here are the details:

@component('mail::table')
| | |
| ------------- |:-------------|
| **Response ID** | {{ $response->id }} |
| **Event ID** | <a href="{{route('forms.guest.index', $response->formParent?->event_id)}}">{{ $response->formParent?->event_id ?? 'N/A' }}</a> |
| **Event Title** | <a href="{{route('forms.guest.index', $response->formParent?->event_id)}}">{{ $response->formParent?->form?->title ?? 'N/A' }}</a> |
| **Form Type** | {{ $response->subform_type }} |
| **Submitted At** | {{ optional($response->created_at)->format('F j, Y g:i A') }} |
@endcomponent

**Submitted Information:**

@component('mail::table')
| Field | Value |
| ------------- |:-------------|
@foreach(($response->response_data ?? []) as $key => $value)
@if(!preg_match('/[0-9a-fA-F-]{36}/', $value ?? '') && !str_contains(strtolower($key), 'uuid'))
| {{ ucwords(str_replace(['_', '-'], ' ', $key)) }} | @if(is_array($value)){{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}@else{{ $value }}@endif |
@endif
@endforeach
@endcomponent

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
