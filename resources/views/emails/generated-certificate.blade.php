<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Generated</title>
</head>
<body style="margin:0; padding:0; background-color:#a8a479; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    @php
        $displayName = trim((string) ($recipientName ?? 'Participant'));
        $displayEventTitle = trim((string) ($eventTitle ?? 'this event'));
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#98b8ab; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#f3f4f1; border-radius:12px; box-shadow:0 8px 20px rgba(47, 58, 36, 0.22); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #707e52; background-color:#5f6f43;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#ffffff;">Your Certificate Is Ready</h2>
                            <p style="margin:8px 0 0; font-size:13px; color:#e7edd7;">Reference Event ID: {{ $eventId }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px; font-size:14px; line-height:1.6; color:#2f3a24;">
                            <p style="margin:0 0 12px;">Dear <strong>{{ $displayName }}</strong>,</p>
                            <p style="margin:0 0 12px;">Thank you for participation on <strong>{{ $displayEventTitle }}</strong>.</p>
                            @if(!empty($eventDate))
                                <p style="margin:0 0 12px;">Event Date: <strong>{{ $eventDate }}</strong></p>
                            @endif
                            <p style="margin:0 0 12px;">Congratulations on completing the activity. Your certificate has been generated successfully and is attached to this email.</p>
                            <p style="margin:0;">We appreciate your participation and hope to see you again in our future events.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #d5d8cb; font-size:13px; color:#2f3a24; background-color:#eef2e1;">
                            <p style="margin:0 0 8px; font-weight:700; color:#4f6038;">Connect with DA-CBC</p>
                            <p style="margin:0 0 6px;">
                                Official Website:
                                <a href="https://dacbc.philrice.gov.ph/" style="color:#4a6b2f; text-decoration:none;">https://dacbc.philrice.gov.ph/</a>
                            </p>
                            <p style="margin:0 0 6px;">
                                Facebook:
                                <a href="https://www.facebook.com/DACropBiotechCenter" style="color:#4a6b2f; text-decoration:none;">https://www.facebook.com/DACropBiotechCenter</a>
                            </p>
                            <p style="margin:0;">
                                Email:
                                <a href="mailto:cropbiotechcenter@gmail.com" style="color:#4a6b2f; text-decoration:none;">cropbiotechcenter@gmail.com</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #d5d8cb; font-size:12px; color:#5f6f43; background-color:#e3e8d7;">
                            This is an automated message from {{ config('app.name') }}. Please do not reply directly to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
