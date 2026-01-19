<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outgoing Transaction Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">Outgoing Transaction Recorded</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">A new outgoing inventory transaction has been logged.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                                <tr>
                                    <td style="padding:8px 0; color:#64748b; width:180px;">Transaction ID</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Item</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->item?->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Brand</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->item?->brand ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Barcode</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->barcode ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Quantity</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->quantity }} {{ $transaction->unit ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Remaining Quantity</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $remainingQuantity }} {{ $transaction->unit ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Requested By</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ trim(($transaction->personnel?->fname ?? '') . ' ' . ($transaction->personnel?->lname ?? '')) ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Purpose</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ $transaction->remarks ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#64748b;">Recorded At</td>
                                    <td style="padding:8px 0; font-weight:600; color:#111827;">{{ optional($transaction->created_at)->format('Y-m-d H:i:s') }}</td>
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
