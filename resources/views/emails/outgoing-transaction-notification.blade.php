<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outgoing Transaction Notification</title>
</head>
<body>
    <h2>New Outgoing Inventory Transaction</h2>

    <p>A new outgoing transaction was recorded.</p>

    <ul>
        <li><strong>Transaction ID:</strong> {{ $transaction->id }}</li>
        <li><strong>Item:</strong> {{ $transaction->item?->name ?? 'N/A' }}</li>
        <li><strong>Brand:</strong> {{ $transaction->item?->brand ?? 'N/A' }}</li>
        <li><strong>Barcode:</strong> {{ $transaction->barcode ?? 'N/A' }}</li>
        <li><strong>Quantity:</strong> {{ $transaction->quantity }}</li>
        <li><strong>Unit:</strong> {{ $transaction->unit ?? 'N/A' }}</li>
        <li><strong>Requested By (Personnel):</strong> {{ $transaction->personnel?->fname ?? '' }} {{ $transaction->personnel?->lname ?? '' }}</li>
        <li><strong>Recorded By (User):</strong> {{ $transaction->user?->name ?? 'N/A' }}</li>
        <li><strong>Project Code:</strong> {{ $transaction->project_code ?? 'N/A' }}</li>
        <li><strong>Remarks:</strong> {{ $transaction->remarks ?? 'N/A' }}</li>
        <li><strong>Recorded At:</strong> {{ optional($transaction->created_at)->format('Y-m-d H:i:s') }}</li>
    </ul>
</body>
</html>
