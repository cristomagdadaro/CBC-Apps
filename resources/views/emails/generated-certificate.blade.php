<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generated</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    @php
        $displayName = trim((string) ($recipientName ?? 'Participant'));
        $displayEventTitle = trim((string) ($eventTitle ?? 'this event'));
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">Your Certificate Is Ready</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">Reference Event ID: {{ $eventId }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">Dear <strong>{{ $displayName }}</strong>,</p>

                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Thank you for participating in the activity <strong>{{ $displayEventTitle }}</strong>.
                            </p>

                            @if (!empty($eventDate))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                                    <tr>
                                        <td style="padding:8px 0; color:#64748b; width:100px;">Event Date</td>
                                        <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $eventDate }}</td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:16px 0; font-size:14px; color:#111827; line-height:1.6;">
                                Congratulations on successfully completing the activity. Your certificate of participation has been generated and is attached to this email for your reference.
                            </p>

                            <p style="margin:0; font-size:14px; color:#111827;">
                                We sincerely appreciate your participation and look forward to welcoming you again in our future events and activities.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #eef2f7; font-size:12px; color:#94a3b8;">
                            <p style="margin:0 0 8px; font-weight:600; color:#64748b;">Connect with DA-CBC</p>
                            <p style="margin:0 0 4px;">Official Website: <a href="https://dacbc.philrice.gov.ph/" style="color:#2563eb; text-decoration:none;">dacbc.philrice.gov.ph</a></p>
                            <p style="margin:0 0 4px;">Facebook: <a href="https://www.facebook.com/DACropBiotechCenter" style="color:#2563eb; text-decoration:none;">facebook.com/DACropBiotechCenter</a></p>
                            <p style="margin:0;">Email: <a href="mailto:cropbiotechcenter@gmail.com" style="color:#2563eb; text-decoration:none;">cropbiotechcenter@gmail.com</a></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #eef2f7; font-size:12px; color:#94a3b8;">
                            This is an automated message from {{ config('app.name') }}. Please do not reply directly to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>