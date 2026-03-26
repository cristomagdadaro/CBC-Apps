<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Inventory Staff & Managers)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">What is an Item?</h4>
                <p class="text-sm">An item is any product, supply, or equipment tracked in the inventory system. Each item belongs to a category and is sourced from a supplier.</p>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Steps to Add a New Item:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Go to <strong>Inventory Management → Items</strong></li>
                    <li>Click the <strong>+ Create Item</strong> button</li>
                    <li>Fill in the item information:
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li><strong>Name:</strong> Item name (required)</li>
                            <li><strong>Brand:</strong> Manufacturer or brand (required)</li>
                            <li><strong>Category:</strong> Select from existing categories (required)</li>
                            <li><strong>Supplier:</strong> Select the supplier (required)</li>
                            <li><strong>Description:</strong> Additional details (optional)</li>
                            <li><strong>Image:</strong> Product image (optional)</li>
                        </ul>
                    </li>
                    <li>Click <strong>Save</strong> to create the item</li>
                    <li>The item now appears in the inventory system</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Important Notes:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Item names and brands must be unique (no duplicates)</li>
                    <li>If category doesn't exist, create it first</li>
                    <li>If supplier doesn't exist, create it first</li>
                    <li>You can edit item details after creation</li>
                </ul>
            </div>
        </section>

        <hr class="my-6">

        <section v-if="showDeveloperSections">
            <h3 class="text-lg font-bold mb-3">For Programmers</h3>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Item Fields & Validation:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">name</code> – String, required</li>
                    <li><code class="bg-gray-100 px-1">brand</code> – String, required</li>
                    <li><code class="bg-gray-100 px-1">category_id</code> – FK to categories, required</li>
                    <li><code class="bg-gray-100 px-1">supplier_id</code> – FK to suppliers, required</li>
                    <li><code class="bg-gray-100 px-1">description</code> – Text, optional</li>
                    <li><code class="bg-gray-100 px-1">image</code> – Long text (base64), optional</li>
                    <li><strong>Unique constraint:</strong> (name, brand) must be unique</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                <h4 class="font-semibold mb-2">API Endpoints:</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">GET /api/inventory/items</code> – List all items</li>
                    <li><code class="bg-gray-100 px-1">POST /api/inventory/items</code> – Create item</li>
                    <li><code class="bg-gray-100 px-1">PUT /api/inventory/items/{id}</code> – Update item</li>
                    <li><code class="bg-gray-100 px-1">DELETE /api/inventory/items/{id}</code> – Delete item</li>
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
    name: 'AddItemTopic',
}
</script>
