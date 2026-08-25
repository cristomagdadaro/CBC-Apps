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
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Non-Programmers (Staff & Managers)</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Incoming Transaction -->
                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                        <LuArrowDownToLine class="h-3.5 w-3.5" />
                        Adding Incoming Transaction
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
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
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuArrowUpFromLine class="h-3.5 w-3.5" />
                        Adding Outgoing Transaction
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
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
                <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                        <LuSettings2 class="h-3.5 w-3.5" />
                        Equipment Logger Availability
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-purple-400" />
                            Set this on each incoming transaction, not the item master.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-purple-400" />
                            Allows the same item to have different sharing rules across batches.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-purple-400" />
                            Logger modes are fetched from the central System Options.
                        </li>
                    </ul>
                </div>

                <!-- Barcodes -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                        <LuBarcode class="h-3.5 w-3.5" />
                        Understanding Barcodes
                    </h4>
                    <ul class="ml-1 space-y-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            Each incoming transaction gets a unique barcode.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            Barcodes help track items and identify them quickly.
                        </li>
                        <li class="flex items-start gap-2">
                            <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-400" />
                            Use the Barcode Printing feature to print physical labels.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- For Programmers -->
        <div
            v-if="showDeveloperSections"
            class="space-y-4 border-t border-slate-100 pt-6 dark:border-slate-800/60">
            <h3 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">For Programmers (Implementation)</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                        <LuNetwork class="h-3.5 w-3.5" />
                        Transaction Fields
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">transac_type</code>
                            'incoming' or 'outgoing'
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">item_id</code>
                            Ref to item
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">quantity</code>
                            Amount of items
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">unit_price</code>
                            Cost per unit (incoming)
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">barcode</code>
                            Unique identifier
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-indigo-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">equipment_logger_mode</code>
                            Sharing availability
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                        <LuCode2 class="h-3.5 w-3.5" />
                        API Endpoints
                    </h4>
                    <ul class="space-y-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-emerald-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">POST</code>
                            /api/inventory/transactions
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-blue-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">PUT</code>
                            /api/inventory/transactions/{id}
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-rose-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">DELETE</code>
                            /api/inventory/transactions/{id}
                        </li>
                        <li>
                            <code class="mr-1 rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-500 shadow-sm dark:border-slate-700 dark:bg-slate-800">GET</code>
                            /generate-barcode/{room}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>

<style scoped></style>
