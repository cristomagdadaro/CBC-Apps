<script>
import TopicLayout from "@/Pages/Manuals/Components/TopicLayout.vue";

export default {
    name: "InventoryTransactionsTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
};
</script>

<template>
    <TopicLayout
        title="Inventory Transactions"
        description="Guidelines for logging incoming and outgoing stock movements, assigning logger availability, and understanding barcode tracking."
        icon="LuNetwork">
        <!-- For Non-Programmers -->
        <div class="space-y-4">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Non-Programmers (Staff & Managers)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Incoming Transaction -->
                <div class="bg-sky-50/50 dark:bg-sky-500/5 border border-sky-100 dark:border-sky-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400 mb-3 flex items-center gap-1.5">
                        <LuArrowDownToLine class="w-3.5 h-3.5" />
                        Adding Incoming Transaction
                    </h4>
                    <ol class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>Go to Inventory Management &rarr; Transactions</li>
                        <li>Select the Item you are receiving</li>
                        <li>Enter the Quantity received and Unit</li>
                        <li>Enter the Unit Price (cost per unit)</li>
                        <li>Select the Storage Location</li>
                        <li>
                            Set the
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Equipment Logger Availability</span>
                        </li>
                        <li>Click Submit to record the transaction</li>
                    </ol>
                </div>

                <!-- Outgoing Transaction -->
                <div class="bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                        <LuArrowUpFromLine class="w-3.5 h-3.5" />
                        Adding Outgoing Transaction
                    </h4>
                    <ol class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 list-decimal list-inside ml-1">
                        <li>Go to Inventory Management &rarr; Transactions</li>
                        <li>Select the Item being issued</li>
                        <li>Select the Personnel receiving it</li>
                        <li>Enter the Quantity being issued</li>
                        <li>Select the Unit</li>
                        <li>Add optional project code or remarks</li>
                        <li>Click Submit to record the transaction</li>
                    </ol>
                </div>

                <!-- Equipment Logger -->
                <div class="bg-purple-50/50 dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-3 flex items-center gap-1.5">
                        <LuSettings2 class="w-3.5 h-3.5" />
                        Equipment Logger Availability
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-purple-400 shrink-0 mt-0.5" />
                            Set this on each incoming transaction, not the item master.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-purple-400 shrink-0 mt-0.5" />
                            Allows the same item to have different sharing rules across batches.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-purple-400 shrink-0 mt-0.5" />
                            Logger modes are fetched from the central System Options.
                        </li>
                    </ul>
                </div>

                <!-- Barcodes -->
                <div class="bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/20 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-1.5">
                        <LuBarcode class="w-3.5 h-3.5" />
                        Understanding Barcodes
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300 ml-1">
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" />
                            Each incoming transaction gets a unique barcode.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" />
                            Barcodes help track items and identify them quickly.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" />
                            Use the Barcode Printing feature to print physical labels.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/60">
            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 ml-1">For Programmers (Implementation)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                        <LuNetwork class="w-3.5 h-3.5" />
                        Transaction Fields
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">transac_type</code>
                            'incoming' or 'outgoing'
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">item_id</code>
                            Ref to item
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">quantity</code>
                            Amount of items
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">unit_price</code>
                            Cost per unit (incoming)
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">barcode</code>
                            Unique identifier
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-indigo-500">equipment_logger_mode</code>
                            Sharing availability
                        </li>
                    </ul>
                </div>

                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                    <h4 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                        <LuCode2 class="w-3.5 h-3.5" />
                        API Endpoints
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-emerald-500">POST</code>
                            /api/inventory/transactions
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-blue-500">PUT</code>
                            /api/inventory/transactions/{id}
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-rose-500">DELETE</code>
                            /api/inventory/transactions/{id}
                        </li>
                        <li>
                            <code class="px-1.5 py-0.5 mr-1 rounded-md bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[0.65rem] font-semibold shadow-sm text-sky-500">GET</code>
                            /generate-barcode/{room}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>

<style scoped></style>
