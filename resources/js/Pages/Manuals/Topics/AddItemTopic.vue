<script>
import TopicLayout from '@/Pages/Manuals/Components/TopicLayout.vue';

export default {
    name: 'AddItemTopic',
    components: {
        TopicLayout
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
}
</script>

<template>
    <TopicLayout
        title="Inventory Items"
        description="An item is any product, supply, or equipment tracked in the inventory system[cite: 7]. Each item belongs to a category and is sourced from a supplier[cite: 7]."
        icon="LuPackage"
    >
                <!-- For Non-Programmers -->
                <div class="space-y-4">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Staff & Managers)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Steps to Add -->
                        <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                                <LuListPlus class="w-3.5 h-3.5" /> Steps to Add a New Item
                            </h4>
                            <ol class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                                <li>Go to <span class="font-semibold text-slate-800 dark:text-slate-200">Inventory Management &rarr; Items</span>[cite: 7]</li>
                                <li>Click the <span class="font-semibold text-slate-800 dark:text-slate-200">+ Create Item</span> button[cite: 7]</li>
                                <li>Fill in the item information:[cite: 7]
                                    <ul class="list-none space-y-1.5 ml-5 mt-2">
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Name:</span> Item name (required)[cite: 7]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Brand:</span> Manufacturer/brand (required)[cite: 7]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Category:</span> Select existing (required)[cite: 7]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Supplier:</span> Select existing (required)[cite: 7]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Description:</span> Additional info (optional)[cite: 7]</li>
                                        <li><span class="font-semibold text-slate-700 dark:text-slate-300">Image:</span> Product image (optional)[cite: 7]</li>
                                    </ul>
                                </li>
                                <li>Click <span class="font-semibold text-slate-800 dark:text-slate-200">Save</span> to create the item[cite: 7]</li>
                                <li>The item now appears in the inventory system[cite: 7]</li>
                            </ol>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                                <LuAlertTriangle class="w-3.5 h-3.5" /> Important Notes
                            </h4>
                            <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Item names and brands must be unique (no duplicates)[cite: 7].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">If category doesn't exist, create it first[cite: 7].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">If supplier doesn't exist, create it first[cite: 7].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">Equipment Logger availability is no longer set on the item form; configure it on each incoming transaction so different stock/project records can have different sharing rules[cite: 7].</span></li>
                                <li class="flex items-start gap-2.5"><LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" /> <span class="leading-relaxed">You can edit item details after creation[cite: 7].</span></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- For Programmers -->
                <div v-if="showDeveloperSections" class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
                    <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Implementation)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Fields & Validation -->
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                                <LuDatabase class="w-3.5 h-3.5" /> Item Fields & Validation
                            </h4>
                            <ul class="space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">name</code> String, required[cite: 7]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">brand</code> String, required[cite: 7]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">category_id</code> FK to categories, required[cite: 7]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">supplier_id</code> FK to suppliers, required[cite: 7]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">description</code> Text, optional[cite: 7]</li>
                                <li class="flex items-center"><code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">image</code> Long text (base64), optional[cite: 7]</li>
                            </ul>
                            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-start gap-2">
                                <LuAlertTriangle class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" />
                                <p class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                    <span class="font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-widest text-[0.6rem]">Unique constraint:</span><br/>
                                    (name, brand) must be unique[cite: 7].
                                </p>
                            </div>
                        </div>

                        <!-- API Endpoints -->
                        <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                            <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                                <LuNetwork class="w-3.5 h-3.5" /> API Endpoints
                            </h4>
                            <ul class="space-y-3.5 text-xs font-medium text-slate-600 dark:text-slate-400">
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/items</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">List all items[cite: 7]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-500">POST</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/items</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Create item[cite: 7]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-amber-500">PUT</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/items/{id}</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Update item[cite: 7]</span>
                                </li>
                                <li>
                                    <div class="flex items-center mb-1">
                                        <code class="px-1.5 py-0.5 mr-2 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-rose-500">DEL</code>
                                        <code class="font-mono text-[0.7rem] text-slate-800 dark:text-slate-200">/api/inventory/items/{id}</code>
                                    </div>
                                    <span class="text-[0.65rem] text-slate-500 ml-10">Delete item[cite: 7]</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

    </TopicLayout>
</template>