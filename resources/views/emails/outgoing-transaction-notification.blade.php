<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Outgoing Transaction</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(15, 23, 42, 0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px; border-bottom:1px solid #eef2f7;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:#0f172a;">New Outgoing Transaction</h2>
                            <p style="margin:6px 0 0; font-size:14px; color:#64748b;">An item has been checked out or issued.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Item: <strong>{{ optional($transaction->item)->name }}</strong>
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Quantity Issued: <strong>{{ abs((float) $transaction->quantity) }} {{ $transaction->unit }}</strong>
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Remaining Quantity: <strong>{{ (float) $remainingQuantity }} {{ $transaction->unit }}</strong>
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Remarks: <strong>{{ $transaction->remarks }}</strong>
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Issued to: <strong>{{ optional($transaction->personnel)->name ?? 'Unknown' }}</strong>
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; color:#111827;">
                                Processed by: <strong>{{ optional($transaction->user)->name ?? 'System' }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px; border-top:1px solid #eef2f7; font-size:12px; color:#94a3b8;">
                            This is an automated notification from {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>