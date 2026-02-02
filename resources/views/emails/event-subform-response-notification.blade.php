<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Subform Response Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">Form Event Notification!</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">A new event response has been submitted.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Response ID</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $response->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Event ID</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $response->formParent?->event_id ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Event Title</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $response->formParent?->form?->title ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Subform Type</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $response->subform_type }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Submitted At</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ optional($response->created_at)->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b; vertical-align:top;">Response Data</td>
                                    <td style="padding:8px 0; font-weight:400; color:#111827; white-space:normal;">
                                        <table style="width:100%; border-collapse:collapse; font-size:13px; background:#f1f5f9; border-radius:6px;">
                                            @foreach(($response->response_data ?? []) as $key => $value)
                                                @if(!preg_match('/[0-9a-fA-F-]{36}/', $value ?? '') && !str_contains(strtolower($key), 'uuid'))
                                                    <tr>
                                                        <td style="padding:6px 8px; color:#64748b; width:160px;">{{ ucwords(str_replace(['_', '-'], ' ', $key)) }}</td>
                                                        <td style="padding:6px 8px; color:#334155; font-weight:600;">
                                                            @if(is_array($value))
                                                                {{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #eef2f7; font-size:12px; color:#94a3b8;">
                            This is an automated notification from {{ env('APP_NAME') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
