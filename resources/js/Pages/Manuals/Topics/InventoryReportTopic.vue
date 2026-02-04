<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Inventory Staff)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">What are Inventory Reports?</h4>
                <p class="text-sm">Reports document specific events or issues related to inventory transactions, such as:</p>
                <ul class="list-disc list-inside space-y-1 text-sm mt-2">
                    <li>Damaged items</li>
                    <li>Missing or lost items</li>
                    <li>Quality issues</li>
                    <li>Discrepancies in quantities</li>
                    <li>Other inventory concerns</li>
                </ul>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">How to File a Report:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Open a <strong>Transaction</strong> that needs a report attached</li>
                    <li>Scroll down to the <strong>Attached Reports</strong> section</li>
                    <li>Click <strong>Add Report</strong> button</li>
                    <li>Fill in the report details:
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li>Report type / category</li>
                            <li>Description of the issue</li>
                            <li>Date and time of incident</li>
                            <li>Any relevant notes or attachments</li>
                        </ul>
                    </li>
                    <li>Click <strong>Submit Report</strong></li>
                    <li>The report is now linked to the transaction and visible to managers</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Report Status Tracking:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Filed:</strong> Report submitted and waiting for review</li>
                    <li><strong>Reviewed:</strong> Manager has reviewed the report</li>
                    <li><strong>Resolved:</strong> Issue has been addressed</li>
                    <li><strong>Archived:</strong> Report filed and no longer active</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section>
            <h3 class="text-lg font-bold mb-3">For Programmers (Implementation)</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Report Model Structure:</h4>
                <p class="text-sm mb-2">Reports are stored and linked to transactions:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">transaction_id</code> – FK to transaction</li>
                    <li><code class="bg-gray-100 px-1">report_data</code> – JSON field for flexible data</li>
                    <li><code class="bg-gray-100 px-1">reported_at</code> – Timestamp of report</li>
                    <li><code class="bg-gray-100 px-1">user_id</code> – Who filed the report</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">Key Files:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Inventory/Transactions/components/TransactionReportAccordion.vue</code> – Report display</li>
                    <li><code class="bg-gray-100 px-1">app/Models/TransactionReport.php</code> – Report model (if exists)</li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'InventoryReportTopic',
}
</script>
