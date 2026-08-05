@component('mail::message')
# Hourly Action Summary Digest

Here is a summary of the automated actions executed in the past hour.

@foreach($groupedLogs as $domain => $logs)
## {{ ucwords(str_replace(['.', '_'], ' ', $domain)) }}

@foreach($logs as $log)
@php
    $meta = $log->payload_meta ?? [];
    $batchPayload = $meta['_batch_payload'] ?? [];
    $title = $batchPayload['item_name'] ?? $batchPayload['equipment_name'] ?? 'Action Executed';
@endphp
- **{{ $title }}**
@if(isset($batchPayload['requester_name']))
  Requester: {{ $batchPayload['requester_name'] }}
@endif
@if(isset($batchPayload['personnel_name']))
  Personnel: {{ $batchPayload['personnel_name'] }}
@endif
@if(isset($batchPayload['status_label']))
  Status: {{ ucfirst($batchPayload['status_label']) }}
@endif
@if(isset($batchPayload['quantity']))
  Quantity: {{ $batchPayload['quantity'] }} {{ $batchPayload['unit'] ?? '' }}
@endif
@endforeach

@endforeach

Thanks,<br>
{{ config('app.name') }}
@endcomponent
