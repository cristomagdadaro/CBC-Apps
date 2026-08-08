@component('mail::message')
# Item Issuance Notice

Hello,

This is to notify you that an item has recently been checked out from the inventory.

@component('mail::table')
| | |
| ------------- |:-------------|
| **Item** | {{ optional($transaction->item)->name }} |
| **Quantity Issued** | {{ abs((float) $transaction->quantity) }} {{ $transaction->unit }} |
| **Remaining Stock** | {{ (float) $remainingQuantity }} {{ $transaction->unit }} |
| **Remarks** | {{ $transaction->remarks ?: 'None' }} |
| **Issued To** | {{ optional($transaction->personnel)->name ?? 'Unknown' }} |
| **Processed By** | {{ optional($transaction->user)->name ?? 'System' }} |
@endcomponent

Best regards,<br>
DA-Crop Biotechnology Center
@endcomponent