<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Procurement & Admin)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">What is a Supplier?</h4>
                <p class="text-sm">A supplier is a vendor or organization that provides items and supplies to your organization. Each supplier stores contact and business information.</p>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Steps to Add a New Supplier:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Go to <strong>Inventory Management → Suppliers</strong></li>
                    <li>Click the <strong>+ New Supplier</strong> button</li>
                    <li>Fill in the supplier information:
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li><strong>Name:</strong> Company or organization name</li>
                            <li><strong>Email:</strong> Contact email address</li>
                            <li><strong>Phone:</strong> Contact phone number</li>
                            <li><strong>Address:</strong> Business address</li>
                            <li><strong>Description:</strong> Additional notes (optional)</li>
                        </ul>
                    </li>
                    <li>Click <strong>Save</strong> to create the supplier</li>
                    <li>The new supplier is now available when creating items or recording transactions</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Best Practices:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Use consistent naming conventions (avoid duplicates)</li>
                    <li>Always include valid contact information</li>
                    <li>Update supplier details regularly</li>
                    <li>Deactivate or soft-delete unused suppliers (don't permanently delete)</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section>
            <h3 class="text-lg font-bold mb-3">For Programmers</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Supplier Fields:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">id</code> – Primary key</li>
                    <li><code class="bg-gray-100 px-1">name</code> – Supplier name (required)</li>
                    <li><code class="bg-gray-100 px-1">email</code> – Contact email</li>
                    <li><code class="bg-gray-100 px-1">phone</code> – Contact phone</li>
                    <li><code class="bg-gray-100 px-1">address</code> – Business address</li>
                    <li><code class="bg-gray-100 px-1">description</code> – Additional info</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">API Endpoints:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">GET /api/inventory/suppliers</code> – List all suppliers</li>
                    <li><code class="bg-gray-100 px-1">POST /api/inventory/suppliers</code> – Create supplier</li>
                    <li><code class="bg-gray-100 px-1">PUT /api/inventory/suppliers/{id}</code> – Update supplier</li>
                    <li><code class="bg-gray-100 px-1">DELETE /api/inventory/suppliers/{id}</code> – Delete supplier</li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'AddSupplierTopic',
}
</script>
