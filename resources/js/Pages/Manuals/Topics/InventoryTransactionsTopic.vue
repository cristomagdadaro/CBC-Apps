<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Warehouse Staff & Managers)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Adding an Incoming Transaction:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Go to <strong>Inventory Management → Transactions → Incoming</strong></li>
                    <li>Select the <strong>Item</strong> you are receiving</li>
                    <li>Enter the <strong>Quantity</strong> received</li>
                    <li>Select the <strong>Unit</strong> (pieces, boxes, kilos, etc.)</li>
                    <li>Enter the <strong>Unit Price</strong> (cost per unit)</li>
                    <li>Select the <strong>Storage Location</strong> (where items are stored)</li>
                    <li><strong>Optional:</strong> Add supplier info, project code, expiration date, and remarks</li>
                    <li>Click <strong>Submit</strong> to record the transaction</li>
                </ol>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Adding an Outgoing Transaction:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Go to <strong>Inventory Management → Transactions → Outgoing</strong></li>
                    <li>Select the <strong>Item</strong> being issued</li>
                    <li>Select the <strong>Personnel</strong> receiving the item</li>
                    <li>Enter the <strong>Quantity</strong> being issued</li>
                    <li>Select the <strong>Unit</strong></li>
                    <li><strong>Optional:</strong> Add project code and remarks</li>
                    <li>Click <strong>Submit</strong> to record the transaction</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Understanding Barcodes:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Each incoming transaction generates a <strong>unique barcode</strong></li>
                    <li>Barcodes help track items and identify them quickly</li>
                    <li>You can scan barcodes to update transactions</li>
                    <li>Use the <strong>Barcode Printing</strong> feature to print labels</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section v-if="showDeveloperSections">
            <h3 class="text-lg font-bold mb-3">For Programmers (Implementation Details)</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Transaction Fields:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">transac_type</code> – 'incoming' or 'outgoing'</li>
                    <li><code class="bg-gray-100 px-1">item_id</code> – Reference to item</li>
                    <li><code class="bg-gray-100 px-1">quantity</code> – Amount of items</li>
                    <li><code class="bg-gray-100 px-1">unit</code> – Unit of measurement</li>
                    <li><code class="bg-gray-100 px-1">unit_price</code> – Cost per unit (incoming only)</li>
                    <li><code class="bg-gray-100 px-1">barcode</code> – Unique identifier</li>
                    <li><code class="bg-gray-100 px-1">storage_location</code> – Where stored (incoming only)</li>
                    <li><code class="bg-gray-100 px-1">personnel_id</code> – Who received/issued (outgoing)</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">API Endpoints:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">POST /api/inventory/transactions</code> – Create transaction</li>
                    <li><code class="bg-gray-100 px-1">PUT /api/inventory/transactions/{id}</code> – Update transaction</li>
                    <li><code class="bg-gray-100 px-1">DELETE /api/inventory/transactions/{id}</code> – Delete transaction</li>
                    <li><code class="bg-gray-100 px-1">GET /api/inventory/transactions/generate-barcode/{room}</code> – Generate barcode</li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    name: 'InventoryTransactionsTopic',
}
</script>
