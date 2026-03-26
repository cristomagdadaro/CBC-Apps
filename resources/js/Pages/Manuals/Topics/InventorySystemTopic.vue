<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Overview</h3>
            <p class="mb-3">The <strong>Inventory System</strong> tracks all items, supplies, equipment, and transactions in your organization. It helps manage stock levels, monitor incoming/outgoing items, and generate reports.</p>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Inventory Managers & Staff)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Main Components:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Items:</strong> All products and supplies in inventory</li>
                    <li><strong>Categories:</strong> Organization groups (Office Supplies, Laboratory Equipment, etc.)</li>
                    <li><strong>Suppliers:</strong> Vendors and sources for items</li>
                    <li><strong>Transactions:</strong> Records of incoming and outgoing items</li>
                    <li><strong>Stock Levels:</strong> Real-time tracking of quantity and status</li>
                </ul>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Accessing the Inventory System:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Go to <strong>Inventory Management</strong> in the main navigation</li>
                    <li>Select the section you need: Items, Suppliers, or Transactions</li>
                    <li>Use filters to search and sort inventory data</li>
                    <li>View detailed information by clicking on any item or transaction</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Stock Level Indicators:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Full (🟢):</strong> Sufficient stock available (75% - 100%)</li>
                    <li><strong>Mid (🟡):</strong> Moderate stock level (25% - 75%)</li>
                    <li><strong>Low (🟠):</strong> Running low on stock (1% - 25%)</li>
                    <li><strong>Empty (🔴):</strong> Stock depleted (0%)</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section v-if="showDeveloperSections">
            <h3 class="text-lg font-bold mb-3">For Programmers (System Architecture)</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Key Models & Files:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">app/Models/Item.php</code> – Item model</li>
                    <li><code class="bg-gray-100 px-1">app/Models/Category.php</code> – Category model</li>
                    <li><code class="bg-gray-100 px-1">app/Models/Supplier.php</code> – Supplier model</li>
                    <li><code class="bg-gray-100 px-1">app/Models/Transaction.php</code> – Transaction model</li>
                    <li><code class="bg-gray-100 px-1">routes/inventory.php</code> – Inventory API routes</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">Key Frontend Components:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Inventory/Items</code> – Item management UI</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Inventory/Transactions</code> – Transaction UI</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Inventory/Supplier</code> – Supplier management UI</li>
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
    name: 'InventorySystemTopic',
}
</script>
