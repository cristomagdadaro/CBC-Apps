@component('mail::message')
# Hourly Activity Summary

Hello,

Here is a quick overview of the automated system actions processed over the past hour.

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

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent
