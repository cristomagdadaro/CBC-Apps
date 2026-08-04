<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Overdue Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
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
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">Equipment Usage Overdue</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">Your equipment session requires immediate attention.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">Dear {{ $personnelName !== '' ? $personnelName : 'User' }},</p>

                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Your {{ $typeLabel }} usage session for <strong>{{ $equipmentName }}</strong> is now overdue.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0; color:#64748b; width:140px;">Started At</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ optional($log->started_at)->format('F j, Y g:i A') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Expected End</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ optional($log->end_use_at)->format('F j, Y g:i A') ?? 'N/A' }}</td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0; font-size:14px; color:#111827;">
                                Please return the equipment or update the expected end of use as soon as possible.
                            </p>

                            @if ($equipmentUrl)
                                <p style="margin:16px 0 0; font-size:14px; color:#111827;">
                                    Open your equipment session here:<br>
                                    <a href="{{ $equipmentUrl }}" style="color:#2563eb; text-decoration:none;">{{ $equipmentUrl }}</a>
                                </p>
                            @endif

                            <p style="margin:16px 0 0; font-size:13px; color:#64748b;">
                                If you already checked out this equipment and still received this email, you may ignore this notice.
                            </p>
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